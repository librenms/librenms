<?php

$temperature_oids = snmpwalk_cache_oid($device, 'sysTempDegC', [], 'ALTA-SYSTEM-MIB');
if (! empty($temperature_oids)) {
    $temperature_modes = snmpwalk_cache_oid($device, 'sysTempMode', [], 'ALTA-SYSTEM-MIB');

    foreach ($temperature_oids as $index => $entry) {
        $value = $entry['sysTempDegC'] ?? null;
        $mode = $temperature_modes[$index]['sysTempMode'] ?? 1;

        if (! is_numeric($value) || (is_numeric($mode) && (int) $mode === 0) || strtolower((string) $mode) === 'disabled') {
            continue;
        }

        $descr = count($temperature_oids) > 1 ? "Temperature $index" : 'Temperature';
        discover_sensor(
            null,
            'temperature',
            $device,
            '.1.3.6.1.4.1.61802.2.1.1.2.' . $index,
            $index,
            'altalabs',
            $descr,
            1,
            1,
            null,
            null,
            null,
            null,
            $value
        );
    }
}
