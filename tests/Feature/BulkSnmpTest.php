<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\User;
use Database\Factories\DeviceGroupFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

/**
 * Feature tests for the bulk SNMP credentials feature.
 */
class BulkSnmpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');

        LibrenmsConfig::set('auth_mechanism', 'mysql');
    }

    private function makeAdmin(): User
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('admin');

        return $user;
    }

    public function testGuestCannotAccessBulkSnmpForm(): void
    {
        $group = DeviceGroupFactory::new()->create();

        $this->get(route('device-group.bulk-snmp.show', $group))
            ->assertRedirect();
    }

    public function testNonAdminSeesFriendlyDeniedPage(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $group = DeviceGroupFactory::new()->create();

        $this->actingAs($user)
            ->get(route('device-group.bulk-snmp.show', $group))
            ->assertOk()
            ->assertSee(__('bulk-snmp.denied.title'));
    }

    public function testNonAdminCannotApply(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
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
        $up = Device::factory()->count(2)->create(['status' => 1]);
        $down = Device::factory()->count(1)->create(['status' => 0]);
        $group->devices()->sync($up->pluck('device_id')->merge($down->pluck('device_id')));

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
