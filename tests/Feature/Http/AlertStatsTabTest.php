<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\AlertLog;
use App\Models\AlertRule;
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
            ->get(route('device.faults.stats', $device->device_id))
            ->assertOk()
            ->assertSee('Alert Stats')
            ->assertSee('critical');
    }

    public function testLegacyUrlRedirects(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get("/device/$device->device_id/alert-stats")
            ->assertRedirect(route('device.faults.stats', $device->device_id));
    }

    public function testUserWithoutAccessGetsForbidden(): void
    {
        $device = Device::factory()->create();

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device.faults.stats', $device->device_id))
            ->assertForbidden();
    }

    public function testGlobalStatsOnlyCountAccessibleDevices(): void
    {
        LibrenmsConfig::set('alert_graph_date_format', '%Y-%m-%d');
        $device = Device::factory()->create();
        $critical = AlertRule::factory()->create(['severity' => 'critical']);
        $warning = AlertRule::factory()->create(['severity' => 'warning']);
        AlertLog::factory()->create(['device_id' => $device->device_id, 'rule_id' => $critical->id, 'state' => 1, 'time_logged' => '2026-10-01 12:00:00']);
        AlertLog::factory()->create(['device_id' => $device->device_id, 'rule_id' => $critical->id, 'state' => 0, 'time_logged' => '2026-10-01 13:00:00']); // cleared, not counted
        AlertLog::factory()->create(['rule_id' => $warning->id, 'state' => 1, 'time_logged' => '2026-10-02 12:00:00']); // another device

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($device->device_id);

        $stats = $this->actingAs($user)->get(route('alert-stats'))->assertOk()->viewData('stats');
        $this->assertSame(['critical'], $stats['groups']);
        $this->assertSame([['x' => '2026-10-01', 'y' => 1, 'group' => 'critical']], $stats['items']);

        $stats = $this->actingAs($this->admin())->get(route('alert-stats'))->assertOk()->viewData('stats');
        $this->assertEqualsCanonicalizing(['critical', 'warning'], $stats['groups']);
    }
}
