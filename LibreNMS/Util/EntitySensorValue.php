<?php

namespace LibreNMS\Util;

/**
 * Validate fixed-point readings from ENTITY-SENSOR-MIB (RFC 3433).
 */
final class EntitySensorValue
{
    /**
     * @param  array<string, mixed>  $sensor
     */
    public static function isErrorCode(array $sensor, int|float $value): bool
    {
        // These sentinel values are only reserved for fixed-point sensor types.
        // Do not filter them from discrete state or unrelated MIB sensors.
        return ($sensor['sensor_type'] ?? null) === 'entity-sensor'
            && in_array($sensor['sensor_class'] ?? null, [
                'voltage', // voltsAC, voltsDC
                'current', // amperes
                'power', // watts
                'freq', // hertz
                'temperature', // celsius
            ], true)
            && ($value == -1000000000 || $value == 1000000000);
    }
}
