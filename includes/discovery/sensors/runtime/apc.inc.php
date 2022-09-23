<?php

$oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.2.2.3.0', '-OsqnUt', '');
d_echo($oids . "\n");
if ($oids) {
    echo ' APC Runtime ';
    [$oid,$current] = explode(' ', $oids);
    $divisor = 6000;
    $type = 'apc';
    $index = 'upsAdvBatteryRunTimeRemaining.0';
    $descr = 'Runtime';
    $low_limit = 5;
    $low_limit_warn = 10;
    $warn_limit = 2000;
    $high_limit = 3000;
    discover_sensor($valid['sensor'], 'runtime', $device, $oid, $index, $type, $descr, $divisor, '1', $low_limit, $low_limit_warn, $warn_limit, $high_limit, $current);
}

// InRow IRRP100
$oids = snmp_get($device, 'airIRRP100GroupSetpointsCoolMetric.0', '-OsqnU', 'PowerNet-MIB');
if ($oids) {
    echo 'APC InRow IRRP100 ';
    // airIRRP100UnitRunHoursAirFilter
    $index = 0;
    $cur_oid = '.1.3.6.1.4.1.318.1.1.13.3.3.1.2.3.1.';
    $current = snmp_get($device, 'airIRRP100UnitRunHoursAirFilter.' . $index, '-Oqv', 'PowerNet-MIB');
    $service_interval = snmp_get($device, 'airIRRP100UnitServiceIntervalAirFilter.' . $index, '-Oqv', 'PowerNet-MIB');
    $alarm_status = snmp_get($device, 'airIRRP100UnitServiceIntervalAirFilterAlarm.' . $index, '-Oqv', 'PowerNet-MIB');
    $multiplier = 60;
    $current = ($current * $multiplier);
    if ($alarm_status == 'enable') {
        $service_interval = ($service_interval * 10080);
    } else {
        $service_interval = null;
    }
    $descr = 'Filter';
    $sensorType = 'apc';

    discover_sensor($valid['sensor'], 'runtime', $device, $cur_oid . $index, 'airIRRP100UnitRunHoursAirFilter.' . $index, $sensorType, $descr, '1', $multiplier, null, null, null, $service_interval, $current);

    // airIRRP100UnitRunHoursFan1
    $index = 0;
    $cur_oid = '.1.3.6.1.4.1.318.1.1.13.3.3.1.2.3.4.';
    $current = snmp_get($device, 'airIRRP100UnitRunHoursFan1.' . $index, '-Oqv', 'PowerNet-MIB');
    $service_interval = snmp_get($device, 'airIRRP100UnitServiceIntervalFans.' . $index, '-Oqv', 'PowerNet-MIB');
    $multiplier = 60;
    $current = ($current * $multiplier);
    $service_interval = ($service_interval * 10080);
    $descr = 'Fan 1';
    $sensorType = 'apc';

    discover_sensor($valid['sensor'], 'runtime', $device, $cur_oid . $index, 'airIRRP100UnitRunHoursFan1.' . $index, $sensorType, $descr, '1', $multiplier, null, null, null, $service_interval, $current);

    // airIRRP100UnitRunHoursFan2
    $index = 0;
    $cur_oid = '.1.3.6.1.4.1.318.1.1.13.3.3.1.2.3.6.';
    $current = snmp_get($device, 'airIRRP100UnitRunHoursFan2.' . $index, '-Oqv', 'PowerNet-MIB');
    $service_interval = snmp_get($device, 'airIRRP100UnitServiceIntervalFans.' . $index, '-Oqv', 'PowerNet-MIB');
    $multiplier = 60;
    $current = ($current * $multiplier);
    $service_interval = ($service_interval * 10080);
    $descr = 'Fan 2';
    $sensorType = 'apc';

    discover_sensor($valid['sensor'], 'runtime', $device, $cur_oid . $index, 'airIRRP100UnitRunHoursFan2.' . $index, $sensorType, $descr, '1', $multiplier, null, null, null, $service_interval, $current);

    // airIRRP100UnitRunHoursCompressor
    $index = 0;
    $cur_oid = '.1.3.6.1.4.1.318.1.1.13.3.3.1.2.3.8.';
    $current = snmp_get($device, 'airIRRP100UnitRunHoursCompressor.' . $index, '-Oqv', 'PowerNet-MIB');
    $service_interval = snmp_get($device, 'airIRRP100UnitServiceIntervalCompressor.' . $index, '-Oqv', 'PowerNet-MIB');
    $multiplier = 60;
    $current = ($current * $multiplier);
    $service_interval = ($service_interval * 10080);
    $descr = 'Compressor';
    $sensorType = 'apc';

    discover_sensor($valid['sensor'], 'runtime', $device, $cur_oid . $index, 'airIRRP100UnitRunHoursCompressor.' . $index, $sensorType, $descr, '1', $multiplier, null, null, null, $service_interval, $current);
}
