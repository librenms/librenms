<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\User;
use LibreNMS\Tests\InMemoryDbTestCase;
use Spatie\Permission\Models\Role;

final class DevicesFilterFieldsTest extends InMemoryDbTestCase
{
    public function testFiltersFollowPermissions(): void
    {
        LibrenmsConfig::set('distributed_poller', true);
        Role::findOrCreate('admin');

        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $this->assertSame(
            ['location_id', 'groups.id', 'poller_group'],
            array_values(array_intersect($this->filterKeys($admin), ['location_id', 'groups.id', 'poller_group']))
        );

        // no permissions, only filters on the devices themselves
        $user = User::factory()->create(['enabled' => 1]);
        $keys = $this->filterKeys($user);
        $this->assertNotContains('groups.id', $keys);
        $this->assertNotContains('poller_group', $keys);
        $this->assertContains('type', $keys);
        $this->assertContains('disabled', $keys);
    }

    /**
     * @return string[]
     */
    private function filterKeys(User $user): array
    {
        $response = $this->actingAs($user)->get(route('devices'));
        $response->assertOk();

        return array_column($response->viewData('filterFields'), 'key');
    }
}
