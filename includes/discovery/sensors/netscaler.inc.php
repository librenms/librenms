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

    $oid = '.1.3.6.1.4.1.5951.4.1.1.41.7.1.2.' . \LibreNMS\Util\Oid::ofString($descr);

    $divisor = 1;
    if (str_contains($descr, 'Temp')) {
        $type = 'temperature';
    } elseif (str_contains($descr, 'Fan')) {
        $type = 'fanspeed';
    } elseif (str_contains($descr, 'Volt')) {
        $divisor = 1000;
        $type = 'voltage';
    } elseif (str_contains($descr, 'Vtt')) {
        $divisor = 1000;
        $type = 'voltage';
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
            current: $current / $divisor
        );
    }
}

unset($ns_sensor_array);
