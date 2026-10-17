<?php

namespace LibreNMS\Tests\Unit\Models;

use App\Models\WirelessSensor;
use LibreNMS\Tests\TestCase;

final class WirelessSensorTest extends TestCase
{
    public function testWirelessSensorAggregator(): void
    {
        $values = ['.1.2.1' => '10', '.1.2.2' => '1', '.1.2.3' => '5 dBm'];

        foreach (['sum' => 16, 'avg' => 16 / 3, 'min' => 1, 'max' => 10, 'unknown' => 16] as $aggregator => $expected) {
            $sensor = new WirelessSensor(['sensor_aggregator' => $aggregator, 'sensor_divisor' => 1, 'sensor_multiplier' => 1]);
            $this->assertEqualsWithDelta($expected, $sensor->fillValue($values)->sensor_current, 0.001, $aggregator);
        }
    }
}
