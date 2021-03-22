<?php
/*
 *
 * Adjusted to account for both traditional dBm sensors, and fake sensors created for some iosxr devices from mW values
 * Adjusted to account for a floating precision value on seperate XR devices rather than a statiaclly set value
 *
*/

// Determine original sensor type by checking entSensorType in same table
$sensor_type = snmp_get($device, str_replace('.1.3.6.1.4.1.9.9.91.1.1.1.1.4', '.1.3.6.1.4.1.9.9.91.1.1.1.1.1', $sensor['sensor_oid']), '-Oqv');
// If sensor was originally a mW value, apply mW conversion logic, ignore if not
if ($sensor_type == 6) {
    // Determine sensor precision by checking entSensorPrecision in the same table 
    $sensor_precision = snmp_get($device, str_replace('.1.3.6.1.4.1.9.9.91.1.1.1.1.4', '.1.3.6.1.4.1.9.9.91.1.1.1.1.3', $sensor['sensor_oid']), '-Oqv');
    $sensor_value = round(10 * log10($sensor_value / (10 ** $sensor_precision) ), 3);
}
