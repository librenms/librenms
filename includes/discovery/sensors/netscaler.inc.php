<?php

echo ' NetScaler ';

echo ' Caching OIDs:';

if (! is_array($ns_sensor_array)) {
    $ns_sensor_array = [];
    echo ' sysHealthCounterValue ';
    $ns_sensor_array = snmpwalk_cache_multi_oid($device, 'sysHealthCounterValue', $ns_sensor_array, 'NS-ROOT-MIB');
}

foreach ($ns_sensor_array as $descr => $data) {
    $current = $data['sysHealthCounterValue'];

    $oid = '.1.3.6.1.4.1.5951.4.1.1.41.7.1.2.' . string_to_oid($descr);

    if (strpos($descr, 'Temp') !== false) {
        $divisor = 0;
        $multiplier = 0;
        $type = 'temperature';
    } elseif (strpos($descr, 'Fan') !== false) {
        $divisor = 0;
        $multiplier = 0;
        $type = 'fanspeed';
    } elseif (strpos($descr, 'Volt') !== false) {
        $divisor = 1000;
        $multiplier = 0;
        $type = 'voltage';
    } elseif (strpos($descr, 'Vtt') !== false) {
        $divisor = 1000;
        $multiplier = 0;
        $type = 'voltage';
    }

    if ($divisor) {
        $current = ($current / $divisor);
    }

    if (is_numeric($current) && $type) {
        discover_sensor(
            $valid['sensor'],
            $type,
            $device,
            $oid,
            $descr,
            'netscaler-health',
            $descr,
            $divisor,
            $multiplier,
            null,
            null,
            null,
            null,
            $current
        );
    }
}

unset($ns_sensor_array);
