<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Sensor;
use App\Models\User;
use App\Models\WirelessSensor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;

final class SensorTableLinksTest extends TestCase
{
    use DatabaseTransactions;

    public function testSensorRowsLinkToDeviceHealthTab(): void
    {
        $device = Device::factory()->create();
        Sensor::factory()->for($device)->create(['sensor_class' => 'temperature']);

        $response = $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->post(route('table.sensors'), ['class' => 'temperature'])
            ->assertOk();

        $this->assertStringContainsString(
            route('device.health', ['device' => $device->device_id, 'metric' => 'temperature']),
            $response->json('rows.0.sensor_descr')
        );
    }

    public function testWirelessRowsLinkToDeviceWirelessTab(): void
    {
        $device = Device::factory()->create();
        WirelessSensor::factory()->for($device)->create(['sensor_class' => 'clients']);

        $response = $this->actingAs(User::factory()->admin()->create(['enabled' => 1]))
            ->post(route('table.wireless'), ['class' => 'clients'])
            ->assertOk();

        $this->assertStringContainsString(
            route('device.wireless', ['device' => $device->device_id, 'metric' => 'clients']),
            $response->json('rows.0.sensor_descr')
        );
    }
}
