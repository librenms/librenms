<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\User;
use LibreNMS\Tests\InMemoryDbTestCase;
use Spatie\Permission\Models\Role;

final class AlertFilterPagesTest extends InMemoryDbTestCase
{
    public function testPagesRequirePermission(): void
    {
        $user = User::factory()->create(['enabled' => 1]);

        $this->actingAs($user)->get(route('faults'))->assertForbidden();
        $this->actingAs($user)->get(route('alert-log'))->assertForbidden();
        $this->actingAs($user)->get(route('ajax.select.alert-rule'))->assertForbidden();
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
            ->assertSee("'device.faults'", false)
            ->assertSee('fa-bar-chart', false)
            ->assertSee(route('device.faults.stats', $device->device_id), false)
            ->assertSee("device_id: $device->device_id", false);

        $this->actingAs($admin)->get(route('device.faults.history', $device->device_id))
            ->assertOk()
            ->assertViewHas('filterFields', fn ($fields) => ! in_array('device.location_id', array_column($fields, 'key')))
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

    public function testRulePickerOnlyListsRulesOnAccessibleDevices(): void
    {
        $device = Device::factory()->create();
        $visible = AlertRule::factory()->create();
        $hidden = AlertRule::factory()->create();
        Alert::factory()->create(['device_id' => $device->device_id, 'rule_id' => $visible->id]);
        Alert::factory()->create(['rule_id' => $hidden->id]); // on another device

        $user = $this->user('user');
        $user->devicesOwned()->attach($device->device_id);

        $this->assertSame([$visible->id], $this->ruleIds($user));
        $this->assertSame([], $this->ruleIds($user, ['id' => $hidden->id]));
        $this->assertEqualsCanonicalizing([$visible->id, $hidden->id], $this->ruleIds($this->user('admin')));
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
    private function ruleIds(User $user, array $params = []): array
    {
        return array_column($this->actingAs($user)->getJson(route('ajax.select.alert-rule', $params))->assertOk()->json('results'), 'id');
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
