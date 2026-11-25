<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Syslog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class SyslogTableTest extends TestCase
{
    use DatabaseTransactions;

    public function testFiltersByRelativeAndAbsoluteDates(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create(['enabled' => 1])->assignRole('admin');
        $device = Device::factory()->create();

        config(['app.timezone' => 'America/Chicago']);
        $previousTimezone = DB::scalar('SELECT @@session.time_zone');
        DB::statement('SET SESSION time_zone = ?', ['+00:00']);

        try {
            $now = Carbon::parse('2026-06-01 12:00:00', 'UTC');
            $this->travelTo($now);

            $factory = Syslog::factory()->state(['device_id' => $device->device_id, 'facility' => 'local0']);
            $factory->create(['timestamp' => '2026-06-01 10:59:59', 'msg' => 'before the range']);
            $factory->create(['timestamp' => '2026-06-01 11:00:00', 'msg' => 'start of the range']);
            $factory->create(['timestamp' => '2026-06-01 12:00:00', 'msg' => 'end of the range']);
            $factory->create(['timestamp' => '2026-06-01 12:00:01', 'msg' => 'after the range']);

            $post = fn (array $filter) => $this->actingAs($admin)->postJson(route('table.syslog'), [
                'current' => 1,
                'rowCount' => 50,
                'device' => $device->device_id,
            ] + $filter)->assertOk()->json('rows.*.msg');

            $this->assertEqualsCanonicalizing(['start of the range', 'end of the range', 'after the range'], $post(['from' => '-1h']));
            $this->assertEqualsCanonicalizing(['before the range', 'start of the range', 'end of the range'], $post(['to' => (string) $now->timestamp]));
            $this->assertEqualsCanonicalizing(['start of the range', 'end of the range'], $post(['from' => '-1h', 'to' => (string) $now->timestamp]));
            $this->assertCount(4, $post([]));
        } finally {
            $this->travelBack();
            DB::statement('SET SESSION time_zone = ?', [$previousTimezone]);
        }
    }

    public function testFilterBarFilters(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create(['enabled' => 1])->assignRole('admin');
        $device = Device::factory()->create();
        $other = Device::factory()->create();

        $factory = Syslog::factory()->state(['facility' => 'local0']);
        $factory->create(['device_id' => $device->device_id, 'program' => 'sshd', 'priority' => 'err', 'msg' => 'login failed']);
        $factory->create(['device_id' => $device->device_id, 'program' => 'cron', 'priority' => 'info', 'msg' => 'job ran']);
        $factory->create(['device_id' => $other->device_id, 'program' => 'sshd', 'priority' => 'info', 'msg' => 'other device']);

        $post = fn (array $filter) => $this->actingAs($admin)->postJson(route('table.syslog'), [
            'current' => 1,
            'rowCount' => 50,
            'filter' => array_replace(['device_id' => ['in' => [$device->device_id, $other->device_id]]], $filter),
        ])->assertOk()->json('rows.*.msg');

        $this->assertEqualsCanonicalizing(['login failed', 'other device'], $post(['program' => ['eq' => 'sshd']]));
        $this->assertSame(['login failed'], $post(['priority' => ['eq' => 'err']]));
        $this->assertSame(['job ran'], $post(['search' => ['contains' => 'ran']]));
        $this->assertSame(['other device'], $post(['device_id' => ['eq' => $other->device_id]]));
    }
}
