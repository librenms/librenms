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

    public function testAuthorizedUserCanRenderInventoryEntPhysicalWithSingleSensor(): void
    {
        $device = Device::factory()->create();
        EntPhysical::factory()->for($device)->create([
            'entPhysicalIndex' => 1,
            'entPhysicalContainedIn' => 0,
            'entPhysicalName' => 'Temperature Sensor 1',
            'entPhysicalClass' => 'sensor',
        ]);
        $sensor = \App\Models\Sensor::factory()->for($device)->create([
            'entPhysicalIndex' => 1,
            'sensor_class' => 'temperature',
            'sensor_descr' => 'Temp Sensor 1',
            'sensor_current' => 35.0,
            'sensor_prev' => 35.0,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'inventory']))
            ->assertOk()
            ->assertSee('Temperature Sensor 1')
            ->assertSee(route('graphs', ['type' => 'sensor_temperature', 'id' => $sensor->sensor_id]))
            ->assertDontSee(__('Sensors') . ':');
    }

    public function testAuthorizedUserCanRenderInventoryEntPhysicalWithMultipleSensors(): void
    {
        $device = Device::factory()->create();
        EntPhysical::factory()->for($device)->create([
            'entPhysicalIndex' => 1,
            'entPhysicalContainedIn' => 0,
            'entPhysicalName' => 'Power Supply 1',
            'entPhysicalClass' => 'powerSupply',
        ]);
        $sensor1 = \App\Models\Sensor::factory()->for($device)->create([
            'entPhysicalIndex' => 1,
            'sensor_class' => 'voltage',
            'sensor_descr' => 'Power Supply 1 Voltage',
            'sensor_current' => 12.0,
            'sensor_prev' => 12.0,
        ]);
        $sensor2 = \App\Models\Sensor::factory()->for($device)->create([
            'entPhysicalIndex' => 1,
            'sensor_class' => 'current',
            'sensor_descr' => 'Power Supply 1 Current',
            'sensor_current' => 5.0,
            'sensor_prev' => 5.0,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'inventory']))
            ->assertOk()
            ->assertSee('Power Supply 1')
            ->assertSee(__('Sensors') . ':')
            ->assertSee('Voltage voltage')
            ->assertSee('Current current')
            ->assertSee(route('graphs', ['type' => 'sensor_voltage', 'id' => $sensor1->sensor_id]))
            ->assertSee(route('graphs', ['type' => 'sensor_current', 'id' => $sensor2->sensor_id]));
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
        $processor = \App\Models\Processor::factory()->for($device)->create([
            'hrDeviceIndex' => 1,
            'processor_descr' => 'Intel Xeon CPU E5-2680',
            'processor_usage' => 25,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'inventory']))
            ->assertOk()
            ->assertSee('Intel Xeon CPU E5-2680')
            ->assertSee('hrDeviceProcessor')
            ->assertSee('running')
            ->assertSee('25%')
            ->assertSee(route('graphs', ['type' => 'processor_usage', 'from' => '-1d', 'id' => $processor->processor_id]));
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
