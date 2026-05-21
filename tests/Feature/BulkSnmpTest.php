<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Database\Factories\DeviceGroupFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Role;

/**
 * Feature tests for the bulk SNMP credentials feature.
 *
 * NOTE: LibreNMS uses Spatie Laravel Permission for roles. These tests
 * assign the 'admin' role explicitly. Adjust to match LibreNMS' own test
 * conventions/seeders if the project provides role helpers.
 */
final class BulkSnmpTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function makeAdmin(): User
    {
        Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function testGuestCannotAccessBulkSnmpForm(): void
    {
        $group = DeviceGroupFactory::new()->create();

        $this->get(route('device-group.bulk-snmp.show', $group))
            ->assertRedirect(); // redirected to login
    }

    public function testNonAdminSeesFriendlyDeniedPage(): void
    {
        $user = User::factory()->create(); // no admin role
        $group = DeviceGroupFactory::new()->create();

        $this->actingAs($user)
            ->get(route('device-group.bulk-snmp.show', $group))
            ->assertOk()
            ->assertSee(__('bulk-snmp.denied.title'));
    }

    public function testNonAdminCannotApply(): void
    {
        $user = User::factory()->create(); // no admin role
        $group = DeviceGroupFactory::new()->create();

        $this->actingAs($user)
            ->postJson(route('device-group.bulk-snmp.apply', $group), [
                'snmpver' => 'v2c',
                'community' => 'whatever',
            ])
            ->assertForbidden();
    }

    public function testAdminCanAccessBulkSnmpForm(): void
    {
        $admin = $this->makeAdmin();
        $group = DeviceGroupFactory::new()->create();

        $this->actingAs($admin)
            ->get(route('device-group.bulk-snmp.show', $group))
            ->assertOk()
            ->assertSee('Bulk SNMP');
    }

    public function testApplyValidatesV3RequiredFields(): void
    {
        $admin = $this->makeAdmin();
        $group = DeviceGroupFactory::new()->create();

        $this->actingAs($admin)
            ->postJson(route('device-group.bulk-snmp.apply', $group), [
                'snmpver' => 'v3',
                // missing authname, authalgo, etc.
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['authname', 'authalgo', 'cryptoalgo', 'authlevel']);
    }

    public function testApplyValidatesV2cRequiredFields(): void
    {
        $admin = $this->makeAdmin();
        $group = DeviceGroupFactory::new()->create();

        $this->actingAs($admin)
            ->postJson(route('device-group.bulk-snmp.apply', $group), [
                'snmpver' => 'v2c',
                // missing community
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['community']);
    }

    public function testApplyUpdatesAllDevicesInGroup(): void
    {
        $admin = $this->makeAdmin();
        $group = DeviceGroupFactory::new()->create();
        $devices = Device::factory()->count(3)->create([
            'snmpver' => 'v2c',
            'community' => 'old_community',
        ]);
        $group->devices()->sync($devices->pluck('device_id'));

        $this->actingAs($admin)
            ->postJson(route('device-group.bulk-snmp.apply', $group), [
                'snmpver' => 'v2c',
                'community' => 'new_community',
            ])
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'success_count' => 3,
                'failed_count' => 0,
            ]);

        foreach ($devices as $device) {
            $this->assertSame('new_community', $device->fresh()->community);
        }
    }

    public function testApplySkipsDownDevicesWhenRequested(): void
    {
        $admin = $this->makeAdmin();
        $group = DeviceGroupFactory::new()->create();
        Device::factory()->count(2)->create(['status' => 1]); // up
        Device::factory()->count(1)->create(['status' => 0]); // down
        $group->devices()->sync(Device::pluck('device_id'));

        $response = $this->actingAs($admin)
            ->postJson(route('device-group.bulk-snmp.apply', $group), [
                'snmpver' => 'v2c',
                'community' => 'updated',
                'skip_down' => true,
            ])
            ->assertOk()
            ->json();

        $this->assertSame(2, $response['total']);
        $this->assertSame(2, $response['success_count']);
    }
}
