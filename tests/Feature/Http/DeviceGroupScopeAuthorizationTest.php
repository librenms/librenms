<?php

/**
 * DeviceGroupScopeAuthorizationTest.php
 *
 * Verifies that device group actions respect the per-device access of the user,
 * not just the global permission.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class DeviceGroupScopeAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    private const string DISCOVERED = '2026-01-01 00:00:00';

    private Device $accessible;
    private Device $other;
    private DeviceGroup $group;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
        $this->accessible = Device::factory()->create(['last_discovered' => self::DISCOVERED]);
        $this->other = Device::factory()->create(['last_discovered' => self::DISCOVERED]);
        $this->group = DeviceGroup::factory()->create(['type' => 'static']);
        $this->group->devices()->attach($this->accessible);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    /**
     * User limited to the accessible device.
     *
     * @param  string[]  $permissions
     */
    private function limitedUser(array $permissions): User
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }
        $user->givePermissionTo($permissions);
        $user->devicesOwned()->attach($this->accessible->device_id);

        return $user;
    }

    /**
     * @param  int[]  $devices
     * @return array{name: string, type: string, devices: int[]}
     */
    private function staticPayload(array $devices): array
    {
        return ['name' => $this->group->name, 'type' => 'static', 'devices' => $devices];
    }

    /**
     * @return int[]
     */
    private function members(DeviceGroup $group): array
    {
        return $group->devices()->pluck('devices.device_id')->sort()->values()->all();
    }

    public function testScopedUserCannotRediscoverGroupWithInaccessibleDevice(): void
    {
        $this->group->devices()->attach($this->other);

        $this->actingAs($this->limitedUser(['device.update']))
            ->postJson(route('device-groups.rediscover', $this->group))
            ->assertForbidden();

        $this->assertNotNull($this->accessible->fresh()->last_discovered);
        $this->assertNotNull($this->other->fresh()->last_discovered);
    }

    public function testScopedUserCannotRediscoverInaccessibleGroup(): void
    {
        $foreign = DeviceGroup::factory()->create(['type' => 'static']);
        $foreign->devices()->attach($this->other);

        $this->actingAs($this->limitedUser(['device.update']))
            ->postJson(route('device-groups.rediscover', $foreign))
            ->assertForbidden();

        $this->assertNotNull($this->other->fresh()->last_discovered);
    }

    public function testScopedUserCanRediscoverFullyAccessibleGroup(): void
    {
        $this->actingAs($this->limitedUser(['device.update']))
            ->postJson(route('device-groups.rediscover', $this->group))
            ->assertOk();

        $this->assertNull($this->accessible->fresh()->last_discovered);
        $this->assertNotNull($this->other->fresh()->last_discovered);
    }

    public function testAdminCanRediscoverGroup(): void
    {
        $this->group->devices()->attach($this->other);

        $this->actingAs($this->admin())
            ->postJson(route('device-groups.rediscover', $this->group))
            ->assertOk();

        $this->assertNull($this->accessible->fresh()->last_discovered);
        $this->assertNull($this->other->fresh()->last_discovered);
    }

    public function testScopedUserCannotAddInaccessibleDeviceToStaticGroup(): void
    {
        $user = $this->limitedUser(['device-group.update']);

        $this->actingAs($user)
            ->put(route('device-groups.update', $this->group), $this->staticPayload([$this->accessible->device_id, $this->other->device_id]))
            ->assertSessionHasErrors('devices');

        $this->assertSame([$this->accessible->device_id], $this->members($this->group));

        // ids that do not exist are rejected the same way
        $this->actingAs($user)
            ->put(route('device-groups.update', $this->group), $this->staticPayload([$this->accessible->device_id, Device::max('device_id') + 1000]))
            ->assertSessionHasErrors('devices');

        $this->assertSame([$this->accessible->device_id], $this->members($this->group));
    }

    public function testScopedUserUpdateKeepsInaccessibleMembers(): void
    {
        $second = Device::factory()->create();
        $this->group->devices()->attach($this->other);
        $user = $this->limitedUser(['device-group.update']);
        $user->devicesOwned()->attach($second->device_id);

        // inaccessible member omitted from the form: kept, accessible devices are synced
        $this->actingAs($user)
            ->put(route('device-groups.update', $this->group), $this->staticPayload([$second->device_id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('device-groups.index'));

        $expected = [$this->other->device_id, $second->device_id];
        sort($expected);
        $this->assertSame($expected, $this->members($this->group));

        // inaccessible member resubmitted by the form: accepted as it is not being added
        $this->actingAs($user)
            ->put(route('device-groups.update', $this->group), $this->staticPayload([$second->device_id, $this->other->device_id]))
            ->assertSessionHasNoErrors();

        $this->assertSame($expected, $this->members($this->group));
    }

    public function testScopedUserCannotCreateStaticGroupWithInaccessibleDevice(): void
    {
        $payload = ['name' => 'scoped-create-test', 'type' => 'static', 'devices' => [$this->other->device_id]];

        $this->actingAs($this->limitedUser(['device-group.create']))
            ->post(route('device-groups.store'), $payload)
            ->assertSessionHasErrors('devices');

        $this->assertFalse(DeviceGroup::where('name', 'scoped-create-test')->exists());
    }

    public function testAdminCanSetAnyDevicesOnStaticGroup(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('device-groups.update', $this->group), $this->staticPayload([$this->other->device_id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('device-groups.index'));

        $this->assertSame([$this->other->device_id], $this->members($this->group));

        $this->actingAs($admin)
            ->post(route('device-groups.store'), ['name' => 'admin-create-test', 'type' => 'static', 'devices' => [$this->accessible->device_id, $this->other->device_id]])
            ->assertSessionHasNoErrors();

        $created = DeviceGroup::where('name', 'admin-create-test')->firstOrFail();
        $expected = [$this->accessible->device_id, $this->other->device_id];
        sort($expected);
        $this->assertSame($expected, $this->members($created));
    }
}
