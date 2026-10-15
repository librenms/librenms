<?php

namespace LibreNMS\Tests\Unit\Enum;

use App\Models\WirelessSensor;
use LibreNMS\Enum\Aggregate;
use LibreNMS\Tests\TestCase;

final class AggregateTest extends TestCase
{
    public function testApply(): void
    {
        $values = [10, 1, 5];

        $this->assertSame(16, Aggregate::Sum->apply($values));
        $this->assertEqualsWithDelta(5.333, Aggregate::Avg->apply($values), 0.001);
        $this->assertSame(1, Aggregate::Min->apply($values));
        $this->assertSame(10, Aggregate::Max->apply($values));
        $this->assertNull(Aggregate::Sum->apply([]));
    }

    public function testWirelessSensorAggregator(): void
    {
        $values = ['.1.2.1' => '10', '.1.2.2' => '1', '.1.2.3' => '5 dBm'];

        foreach (['sum' => 16, 'avg' => 16 / 3, 'min' => 1, 'max' => 10, 'unknown' => 16] as $aggregator => $expected) {
            $sensor = new WirelessSensor(['sensor_aggregator' => $aggregator, 'sensor_divisor' => 1, 'sensor_multiplier' => 1]);
            $this->assertEqualsWithDelta($expected, $sensor->fillValue($values)->sensor_current, 0.001, $aggregator);
        }
    }
}
