<?php

namespace LibreNMS\Tests\Feature\Http;

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

    public function testLegacyFaultUrlsRedirectToFilters(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)->get('/alerts/rule_id=3/acknowledged=0')
            ->assertRedirect(route('faults', ['filter' => ['rule_id' => ['eq' => 3], 'state' => ['neq' => 2]]]));

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
                'state' => ['eq' => 0],
                'rule.severity' => ['in' => 'critical'],
            ]]));

        $this->actingAs($admin)->get('/alert-log?filter[state][in]=0,1')
            ->assertOk()
            ->assertViewHas('filter', ['state' => ['in' => '0,1']]);
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
     * @return string[]
     */
    private function filterKeys(User $user, string $route): array
    {
        $response = $this->actingAs($user)->get(route($route));
        $response->assertOk();

        return array_column($response->viewData('filterFields'), 'key');
    }
}
