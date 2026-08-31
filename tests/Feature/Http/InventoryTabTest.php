<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\EntPhysical;
use App\Models\HrDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class InventoryTabTest extends TestCase
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

    public function testAuthorizedUserCanRenderInventoryEntPhysicalTab(): void
    {
        $device = Device::factory()->create();
        EntPhysical::factory()->for($device)->create([
            'entPhysicalIndex' => 1,
            'entPhysicalContainedIn' => 0,
            'entPhysicalName' => 'Chassis 1',
            'entPhysicalModelName' => 'Catalyst 3850',
            'entPhysicalClass' => 'chassis',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'inventory']))
            ->assertOk()
            ->assertSee('Chassis 1')
            ->assertSee('Catalyst 3850');
    }

    public function testAuthorizedUserCanRenderInventoryEntPhysicalWithSensor(): void
    {
        $device = Device::factory()->create();
        EntPhysical::factory()->for($device)->create([
            'entPhysicalIndex' => 1,
            'entPhysicalContainedIn' => 0,
            'entPhysicalName' => 'Power Supply 1',
            'entPhysicalClass' => 'powerSupply',
        ]);
        $sensor = \App\Models\Sensor::factory()->for($device)->create([
            'entPhysicalIndex' => 1,
            'sensor_class' => 'voltage',
            'sensor_descr' => 'PSU 1 Voltage',
            'sensor_current' => 12.0,
            'sensor_prev' => 12.0,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'inventory']))
            ->assertOk()
            ->assertSee('Power Supply 1')
            ->assertSee('PSU 1 Voltage voltage')
            ->assertSee(route('graphs', ['type' => 'sensor_voltage', 'id' => $sensor->sensor_id]));
    }

    public function testAuthorizedUserCanRenderInventoryHrDeviceTab(): void
    {
        $device = Device::factory()->create();
        HrDevice::factory()->for($device)->create([
            'hrDeviceIndex' => 1,
            'hrDeviceDescr' => 'Intel Xeon CPU E5-2680',
            'hrDeviceType' => 'hrDeviceProcessor',
            'hrDeviceStatus' => 'running',
            'hrProcessorLoad' => 25,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'inventory']))
            ->assertOk()
            ->assertSee('Intel Xeon CPU E5-2680')
            ->assertSee('hrDeviceProcessor')
            ->assertSee('running')
            ->assertSee('25%');
    }

    public function testUserWithoutInventoryPermissionGetsForbidden(): void
    {
        $device = Device::factory()->create();
        EntPhysical::factory()->for($device)->create([
            'entPhysicalIndex' => 1,
            'entPhysicalContainedIn' => 0,
            'entPhysicalName' => 'Chassis 1',
        ]);

        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('device', ['device' => $device, 'tab' => 'inventory']))
            ->assertForbidden();
    }
}
