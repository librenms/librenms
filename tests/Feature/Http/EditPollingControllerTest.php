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
                    'port_association_mode' => 'ifName',
                ],
            ]
        );

        $response->assertRedirect();
        $this->assertEquals(PortAssociationMode::getId('ifName'), $device->fresh()->port_association_mode);
    }
}
