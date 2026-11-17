<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\User;
use App\Models\WirelessSensor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class WirelessTabTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    public function testOverviewShowsClassGraphs(): void
    {
        $device = Device::factory()->create();
        WirelessSensor::factory()->for($device)->create(['sensor_class' => 'clients', 'sensor_descr' => 'Radio <1>']);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'wireless']))
            ->assertOk()
            ->assertSee('type=device_wireless_clients', false)
            ->assertDontSee('Radio');
    }

    public function testMetricListsSensors(): void
    {
        $device = Device::factory()->create();
        WirelessSensor::factory()->for($device)->create(['sensor_class' => 'clients', 'sensor_descr' => 'Radio <1>', 'sensor_current' => 7]);
        WirelessSensor::factory()->for($device)->create(['sensor_class' => 'snr', 'sensor_descr' => 'Other Sensor']);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'wireless', 'metric' => 'clients']))
            ->assertOk()
            ->assertSee('Radio &lt;1&gt;', false)
            ->assertDontSee('Other Sensor')
            ->assertSee('type=wireless_clients', false);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
