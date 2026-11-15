<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Syslog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
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

        Syslog::factory()->create(['device_id' => $device->device_id, 'timestamp' => Carbon::now()->subHours(2), 'msg' => 'recent message']);
        Syslog::factory()->create(['device_id' => $device->device_id, 'timestamp' => Carbon::now()->subDays(3), 'msg' => 'old message']);

        $post = fn (array $filter) => $this->actingAs($admin)->postJson(route('table.syslog'), [
            'current' => 1,
            'rowCount' => 50,
            'device' => $device->device_id,
        ] + $filter)->assertOk()->json('rows.*.msg');

        $this->assertSame(['recent message'], $post(['from' => '-1d']));
        $this->assertSame(['old message'], $post(['to' => (string) Carbon::now()->subDay()->timestamp]));
        $this->assertCount(2, $post([]));
    }

    public function testFilterBarFilters(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create(['enabled' => 1])->assignRole('admin');
        $device = Device::factory()->create();
        $other = Device::factory()->create();

        Syslog::factory()->create(['device_id' => $device->device_id, 'program' => 'sshd', 'priority' => 'err', 'msg' => 'login failed']);
        Syslog::factory()->create(['device_id' => $device->device_id, 'program' => 'cron', 'priority' => 'info', 'msg' => 'job ran']);
        Syslog::factory()->create(['device_id' => $other->device_id, 'program' => 'sshd', 'priority' => 'info', 'msg' => 'other device']);

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
