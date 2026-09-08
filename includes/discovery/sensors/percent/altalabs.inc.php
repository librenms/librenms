<?php

$fan_duty_oids = snmpwalk_cache_oid($device, 'sysFanDuty', [], 'ALTA-SYSTEM-MIB');
if (! empty($fan_duty_oids)) {
    $fan_modes = snmpwalk_cache_oid($device, 'sysFanMode', [], 'ALTA-SYSTEM-MIB');

    foreach ($fan_duty_oids as $index => $entry) {
        $duty = $entry['sysFanDuty'] ?? null;
        $mode = $fan_modes[$index]['sysFanMode'] ?? 1;

        if (! is_numeric($duty) || (is_numeric($mode) && (int) $mode === 0) || strtolower((string) $mode) === 'disabled') {
            continue;
        }

        discover_sensor(
            null,
            'percent',
            $device,
            '.1.3.6.1.4.1.61802.2.2.1.2.' . $index,
            'fan-duty.' . $index,
            'altalabs-fan-duty',
            'Fan Duty ' . $index,
            255,
            100,
            null,
            null,
            null,
            null,
            round(((float) $duty / 255) * 100),
            'snmp',
            null,
            null,
            'round'
        );
    }
}
