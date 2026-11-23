<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\EntitySensorValue;

final class EntitySensorValueTest extends TestCase
{
    public function testRejectsFixedPointUnderflowAndOverflow(): void
    {
        foreach (['temperature', 'voltage', 'current', 'power', 'freq'] as $class) {
            $sensor = ['sensor_type' => 'entity-sensor', 'sensor_class' => $class];

            $this->assertTrue(EntitySensorValue::isErrorCode($sensor, -1000000000));
            $this->assertTrue(EntitySensorValue::isErrorCode($sensor, 1000000000));
            $this->assertTrue(EntitySensorValue::isErrorCode($sensor, -1000000000.0));
            $this->assertTrue(EntitySensorValue::isErrorCode($sensor, 1000000000.0));
        }
    }

    public function testAcceptsRealMeasurementsIncludingZeroAndNegativeValues(): void
    {
        $sensor = ['sensor_type' => 'entity-sensor', 'sensor_class' => 'temperature'];

        foreach ([0, 230, -1000, -999999999, 999999999] as $value) {
            $this->assertFalse(EntitySensorValue::isErrorCode($sensor, $value));
        }
    }

    public function testDoesNotFilterOtherSensorTypesOrDiscreteValues(): void
    {
        foreach (['state', 'fanspeed', 'humidity', 'dbm'] as $class) {
            $sensor = ['sensor_type' => 'entity-sensor', 'sensor_class' => $class];
            $this->assertFalse(EntitySensorValue::isErrorCode($sensor, -1000000000));
            $this->assertFalse(EntitySensorValue::isErrorCode($sensor, 1000000000));
        }

        $otherSensor = ['sensor_type' => 'some-other-mib', 'sensor_class' => 'temperature'];
        $this->assertFalse(EntitySensorValue::isErrorCode($otherSensor, -1000000000));
        $this->assertFalse(EntitySensorValue::isErrorCode($otherSensor, 1000000000));
    }
}
