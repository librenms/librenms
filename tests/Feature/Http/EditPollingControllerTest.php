<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\User;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class EditPollingControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->dbSetUp();

        Role::findOrCreate('admin');
        Permission::findOrCreate('device.update');
        Permission::findOrCreate('secret.create');
        Permission::findOrCreate('secret.update');
        Permission::findOrCreate('secret.view');
        Permission::findOrCreate('secret.delete');
    }

    protected function tearDown(): void
    {
        $this->dbTearDown();
        parent::tearDown();
    }

    public function testUpdateSnmpPollingMethodUpdatesPortAssociationMode(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.update');

        $device = Device::factory()->create([
            'port_association_mode' => PortAssociationMode::getId('ifIndex'),
        ]);

        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'settings' => [
                'transport' => 'udp',
                'port' => 161,
                'timeout' => 1,
                'retries' => 0,
                'max_repeaters' => 0,
                'max_oid' => 10,
                'port_association_mode' => 'ifIndex',
            ],
        ]);

        $response = $this->actingAs($admin)->put(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'snmp']),
            [
                'enabled' => '1',
                'affects_availability' => '1',
                'settings' => [
                    'transport' => 'udp',
                    'port' => 161,
                    'timeout' => 1,
                    'retries' => 0,
                    'max_repeaters' => 0,
                    'max_oid' => 10,
                    'port_association_mode' => 'ifName',
                ],
            ]
        );

        $response->assertRedirect();
        $this->assertEquals(PortAssociationMode::getId('ifName'), $device->fresh()->port_association_mode);
    }

    public function testStorePollingMethodRejectsDuplicateDefaultSecretDescription(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $device = Device::factory()->create(['hostname' => 'test-device.example.com']);

        // Pre-create a secret that has the exact default description
        \App\Models\Secret::create([
            'description' => 'SNMP test-device.example.com',
            'secret_type' => \LibreNMS\Enum\SecretType::Snmp,
            'default' => false,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->post(
            route('device.edit.polling.store', ['device' => $device]),
            [
                'method_type' => 'snmp',
                'credential_mode' => 'new',
                'description' => 'SNMP test-device.example.com',
                'secret_data' => [
                    'version' => 'v2c',
                    'community' => 'private',
                ],
                'settings' => [
                    'transport' => 'udp',
                    'port' => 161,
                    'timeout' => 1,
                    'retries' => 0,
                    'max_repeaters' => 0,
                    'max_oid' => 10,
                    'port_association_mode' => 'ifIndex',
                ],
            ]
        );

        $response->assertSessionHasErrors(['description']);
    }

    public function testStorePollingMethodRejectsDuplicateCustomSecretDescription(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $device = Device::factory()->create(['hostname' => 'test-device2.example.com']);

        \App\Models\Secret::create([
            'description' => 'Existing Custom Description',
            'secret_type' => \LibreNMS\Enum\SecretType::Snmp,
            'default' => false,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->post(
            route('device.edit.polling.store', ['device' => $device]),
            [
                'method_type' => 'snmp',
                'credential_mode' => 'new',
                'description' => 'Existing Custom Description',
                'secret_data' => [
                    'version' => 'v2c',
                    'community' => 'private',
                ],
                'settings' => [
                    'transport' => 'udp',
                    'port' => 161,
                    'timeout' => 1,
                    'retries' => 0,
                    'max_repeaters' => 0,
                    'max_oid' => 10,
                    'port_association_mode' => 'ifIndex',
                ],
            ]
        );

        $response->assertSessionHasErrors(['description']);
    }
}
