<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Http\Controllers\Device\Tabs\OverviewController;
use App\Models\Device;
use App\Models\Sensor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Enum\SensorType;
use LibreNMS\Tests\TestCase;

final class DeviceOverviewSensorNamesTest extends TestCase
{
    use RefreshDatabase;

    public function testStateSensorNamesAreNotShortenedLikeInterfaces(): void
    {
        $device = Device::factory()->create();
        Sensor::factory()->create([
            'device_id' => $device->device_id,
            'sensor_class' => SensorType::State->value,
            'sensor_index' => 'portDataStatus.3',
            'sensor_descr' => '3: Serial Port 3',
            'group' => 'Serial Port',
        ]);
        Sensor::factory()->create([
            'device_id' => $device->device_id,
            'sensor_class' => SensorType::Dbm->value,
            'sensor_index' => 'rx.1',
            'sensor_descr' => 'Serial0/0 Rx Power',
        ]);

        $controller = new OverviewController();
        $method = new \ReflectionMethod($controller, 'groupSensors');
        $descriptions = $method->invoke($controller, $device, $device->sensors()->get())
            ->flatten(1)
            ->pluck('description')
            ->all();

        $this->assertContains('3: Serial Port 3', $descriptions);
        $this->assertContains('Se0/0 Rx Power', $descriptions);
    }
}
