<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class AlertStatsTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function testAuthorizedUserCanRenderAlertStatsTab(): void
    {
        $device = Device::factory()->create();

        $ruleId = DB::table('alert_rules')->insertGetId([
            'severity' => 'critical',
            'extra' => '',
            'disabled' => 0,
            'name' => 'Test Rule',
            'query' => '',
            'builder' => '',
        ]);

        DB::table('alert_log')->insert([
            'device_id' => $device->device_id,
            'rule_id' => $ruleId,
            'state' => 1,
            'time_logged' => now(),
            'details' => '',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'alert-stats']))
            ->assertOk()
            ->assertSee('Device Alerts')
            ->assertSee('critical');
    }

    public function testUserWithoutAccessGetsForbidden(): void
    {
        $device = Device::factory()->create();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device', ['device' => $device, 'tab' => 'alert-stats']))
            ->assertForbidden();
    }
}
