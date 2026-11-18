<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Alert;
use App\Models\AlertFault;
use App\Models\AlertLog;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\User;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\InMemoryDbTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class AlertFilterPagesTest extends InMemoryDbTestCase
{
    public function testPagesRequirePermission(): void
    {
        $user = User::factory()->create(['enabled' => 1]);

        $this->actingAs($user)->get(route('faults'))->assertForbidden();
        $this->actingAs($user)->get(route('alert-log'))->assertForbidden();
        $this->actingAs($user)->get(route('ajax.select.alert-rule'))->assertForbidden();
        $this->get(route('ajax.select.alert-log-rule'))->assertForbidden();

        $user->givePermissionTo(Permission::findOrCreate('alert-log.view'));
        $this->getJson(route('ajax.select.alert-log-rule'))->assertOk();
        $this->getJson(route('ajax.select.alert-rule'))->assertForbidden();
        $this->getJson(route('ajax.select.alert-log-rule', ['device' => 0]))->assertUnprocessable();
    }

    public function testFilterFieldsFollowPermissions(): void
    {
        $admin = $this->user('admin');
        foreach (['faults', 'alert-log'] as $page) {
            $keys = $this->filterKeys($admin, $page);
            $this->assertContains('device.location_id', $keys);
            $this->assertContains('device.groups.id', $keys);
        }

        $user = $this->user('user');
        foreach (['faults', 'alert-log'] as $page) {
            $keys = $this->filterKeys($user, $page);
            $this->assertContains('search', $keys);
            $this->assertContains('rule_id', $keys);
        }
        $this->actingAs($user)->get(route('ajax.select.alert-rule'))->assertOk();
    }

    public function testDeviceFaultPages(): void
    {
        $device = Device::factory()->create();
        $admin = $this->user('admin');

        $this->actingAs($admin)->get(route('device.faults', $device->device_id))
            ->assertOk()
            ->assertViewHas('filterFields', fn ($fields) => ! in_array('device_id', array_column($fields, 'key')))
            ->assertViewHas('filterFields', fn ($fields) => in_array(route('ajax.select.alert-rule', ['device' => $device->device_id]), array_column($fields, 'endpoint')))
            ->assertSee("'device.faults'", false)
            ->assertSee('fa-bar-chart', false)
            ->assertSee(route('device.faults.stats', $device->device_id), false)
            ->assertSee("device_id: $device->device_id", false);

        $this->actingAs($admin)->get(route('device.faults.history', $device->device_id))
            ->assertOk()
            ->assertViewHas('filterFields', fn ($fields) => ! in_array('device.location_id', array_column($fields, 'key')))
            ->assertViewHas('filterFields', fn ($fields) => in_array(route('ajax.select.alert-log-rule', ['device' => $device->device_id]), array_column($fields, 'endpoint')))
            ->assertSee("'device.alert-log'", false)
            ->assertSee("device_id: $device->device_id", false);

        $this->actingAs($this->user('user'))->get(route('device.faults', $device->device_id))->assertForbidden();
    }

    public function testLegacyDeviceAlertUrlsRedirect(): void
    {
        $id = Device::factory()->create()->device_id;
        $admin = $this->user('admin');

        $this->actingAs($admin)->get("/device/$id/alerts")->assertRedirect(route('device.faults', $id));
        $this->actingAs($admin)->get("/device/$id/alerts/section=alert-log?filter[state][eq]=ok")
            ->assertRedirect(route('device.faults.history', ['device' => $id, 'filter' => ['state' => ['eq' => 'ok']]]));
    }

    public function testRulePickersUseAccessibleFaultsOrHistoryRows(): void
    {
        $device = Device::factory()->create(['disabled' => 0]);
        $other = Device::factory()->create(['disabled' => 0]);
        $visibleFault = AlertRule::factory()->create();
        $hiddenFault = AlertRule::factory()->create();
        $visibleHistory = AlertRule::factory()->create();
        $hiddenHistory = AlertRule::factory()->create();
        $closed = AlertRule::factory()->create();
        $unevaluated = AlertRule::factory()->create();
        $unevaluated->devices()->attach($device);
        foreach ([[$visibleFault, $device, 1], [$hiddenFault, $other, 1], [$closed, $device, 0]] as [$rule, $faultDevice, $open]) {
            AlertFault::create([
                'rule_id' => $rule->id,
                'device_id' => $faultDevice->device_id,
                'entity_key' => (string) $faultDevice->device_id,
                'state' => AlertState::ACTIVE,
                'open' => $open,
            ]);
        }
        AlertLog::factory()->create(['rule_id' => $visibleHistory->id, 'device_id' => $device->device_id]);
        AlertLog::factory()->create(['rule_id' => $hiddenHistory->id, 'device_id' => $other->device_id]);

        $user = $this->user('user');
        $user->devicesOwned()->attach($device->device_id);
        $user->givePermissionTo(Permission::findOrCreate('alert-rule.viewAll'));
        $admin = $this->user('admin');

        $this->assertSame([$visibleFault->id], $this->ruleIds($user));
        $this->assertSame([], $this->ruleIds($user, ['id' => $hiddenFault->id]));
        $this->assertSame([$visibleHistory->id], $this->ruleIds($user, route: 'ajax.select.alert-log-rule'));
        $this->getJson(route('ajax.select.alert-log-rule', ['term' => $visibleHistory->name]))
            ->assertOk()->assertJsonPath('results.0.text', $visibleHistory->name);
        $this->assertEqualsCanonicalizing([$visibleFault->id, $hiddenFault->id], $this->ruleIds($admin));
        $this->assertEqualsCanonicalizing([$visibleHistory->id, $hiddenHistory->id], $this->ruleIds($admin, route: 'ajax.select.alert-log-rule'));
        $this->assertSame([$hiddenHistory->id], $this->ruleIds($admin, ['device' => $other->device_id], 'ajax.select.alert-log-rule'));
    }

    public function testAlertMenuUsesAlertsOnAccessibleDevices(): void
    {
        $accessible = Device::factory()->create(['disabled' => 0, 'ignore' => 0]);
        $inaccessible = Device::factory()->create(['disabled' => 0, 'ignore' => 0]);
        Alert::factory()->for(AlertRule::factory()->state(['severity' => 'warning']), 'rule')->create(['device_id' => $accessible->device_id]);
        Alert::factory()->for(AlertRule::factory()->state(['severity' => 'critical']), 'rule')->create(['device_id' => $inaccessible->device_id]);

        $user = $this->user('user');
        $user->devicesOwned()->attach($accessible);
        $user->givePermissionTo(Permission::findOrCreate('alert-rule.viewAll'));

        $this->actingAs($user)->get(route('faults'))->assertOk()->assertSee('fa-exclamation-circle text-warning fa-fw fa-lg');
        $this->actingAs($this->user('admin'))->get(route('faults'))->assertOk()->assertSee('fa-exclamation-circle text-danger fa-fw fa-lg');
    }

    public function testLegacyFaultUrlsRedirectToFilters(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)->get('/alerts/rule_id=3/acknowledged=0')
            ->assertRedirect(route('faults', ['filter' => ['rule_id' => ['eq' => 3], 'state' => ['neq' => 'acknowledged']]]));

        // an explicit state is kept, it already implies acknowledged/fired
        $this->actingAs($admin)->get('/alerts/state=3/acknowledged=0')
            ->assertRedirect(route('faults', ['filter' => ['state' => ['eq' => 'worse']]]));

        $this->actingAs($admin)->get('/alerts/fired=1/acknowledged=0')
            ->assertRedirect(route('faults', ['filter' => ['state' => ['eq' => 'alerted']]]));

        $this->actingAs($admin)->get('/alerts/alert_id=5')
            ->assertRedirect(route('faults', ['alert_id' => 5]));

        $this->actingAs($admin)->get('/alerts')->assertOk();
    }

    public function testLegacyAlertLogUrlsRedirectToFilters(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)->get('/alert-log?device_id=5&state=0&severity[]=3&severity[]=9')
            ->assertRedirect(route('alert-log', ['filter' => [
                'device_id' => ['eq' => 5],
                'state' => ['eq' => 'ok'],
                'rule.severity' => ['in' => 'critical'],
            ]]));

        $this->actingAs($admin)->get('/alert-log?filter[state][in]=ok,alerted')
            ->assertOk()
            ->assertViewHas('filter', ['state' => ['in' => 'ok,alerted']]);
    }

    public function testInvalidFiltersAreRejected(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)->postJson(route('table.alerts'), ['filter' => ['search' => ['contains' => ['a']]]])
            ->assertUnprocessable();
        $this->actingAs($admin)->postJson(route('table.alertlog'), ['filter' => ['state' => ['bogus' => '1']]])
            ->assertUnprocessable();
    }

    private function user(string $role): User
    {
        Role::findOrCreate($role);
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole($role);

        return $user;
    }

    /**
     * @param  array<string, int>  $params
     * @return int[]
     */
    private function ruleIds(User $user, array $params = [], string $route = 'ajax.select.alert-rule'): array
    {
        return array_column($this->actingAs($user)->getJson(route($route, $params))->assertOk()->json('results'), 'id');
    }

    /**
     * @return string[]
     */
    private function filterKeys(User $user, string $route): array
    {
        $response = $this->actingAs($user)->get(route($route));
        $response->assertOk();

        return array_column($response->viewData('filterFields'), 'key');
    }
}
