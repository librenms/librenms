<?php

namespace LibreNMS\Tests\Unit\Observers;

use App\Models\Sensor;
use App\Observers\SensorObserver;
use LibreNMS\Tests\TestCase;

class SensorObserverTest extends TestCase
{
    public function test_discovery_clears_legacy_guessed_temperature_low_limit(): void
    {
        $sensor = $this->sensorWithOriginalLimits(80.0, 50.0);

        $this->observer()->updating($sensor);

        $this->assertSame(80.0, $sensor->sensor_limit);
        $this->assertNull($sensor->sensor_limit_low);
    }

    public function test_discovery_preserves_non_legacy_temperature_limits(): void
    {
        $sensor = $this->sensorWithOriginalLimits(90.0, 5.0);

        $this->observer()->updating($sensor);

        $this->assertSame(90.0, $sensor->sensor_limit);
        $this->assertSame(5.0, $sensor->sensor_limit_low);
    }

    public function test_discovery_preserves_user_customized_temperature_limits(): void
    {
        $sensor = $this->sensorWithOriginalLimits(80.0, 50.0, 'Yes');

        $this->observer()->updating($sensor);

        $this->assertSame(80.0, $sensor->sensor_limit);
        $this->assertSame(50.0, $sensor->sensor_limit_low);
    }

    private function observer(): SensorObserver
    {
        return new SensorObserver($this->app);
    }

    private function sensorWithOriginalLimits(float $high, float $low, string $custom = 'No'): Sensor
    {
        $sensor = new Sensor([
            'sensor_class' => 'temperature',
            'sensor_current' => 40.0,
            'sensor_limit' => $high,
            'sensor_limit_low' => $low,
        ]);
        $sensor->sensor_custom = $custom;
        $sensor->syncOriginal();

        // Discovery modules use null when the hardware supplies no limits.
        $sensor->sensor_limit = null;
        $sensor->sensor_limit_low = null;

        return $sensor;
    }
}
