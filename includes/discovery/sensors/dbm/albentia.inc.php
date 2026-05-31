<?php
//
// Albentia: Target RSSI (config setpoint, single global value per BS).
// All sectors return the same value, so publish only the first row.
// Not exposed in Wireless > RSSI on purpose — that dashboard is for measured
// signal, not configuration targets.

$radio = (array) snmpwalk_cache_oid(
    $device, 'radioInfoTable', [], 'ALBENTIA-AS-MIB', 'albentia', '-OteQUsb'
);

foreach ($radio as $idx_enc => $row) {
    $rssi = $row['radioInfoTargetRSSI'] ?? null;
    if ($rssi === null || $rssi === '') {
        continue;
    }

    discover_sensor(
        null, 'dbm', $device,
        '.1.3.6.1.4.1.28087.12.10.10.5.1.7.' . $idx_enc,
        'radioInfoTargetRSSI',
        'albentia',
        'Target RSSI',
        1, 1, null, null, null, null,
        (int) $rssi
    );
    break;
}

unset($radio, $idx_enc, $row, $rssi);
