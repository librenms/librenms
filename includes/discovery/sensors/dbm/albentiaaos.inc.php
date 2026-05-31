<?php

/**
 * Albentia: Target RSSI (config setpoint, single global value per BS).
 *
 * All sectors return the same value, so publish only the first row.
 * Not exposed in Wireless > RSSI on purpose — that dashboard is for measured
 * signal, not configuration targets.
 *
 * @var array<string, mixed> $device  Provided by the discovery loop in
 *                           includes/discovery/functions.inc.php
 */
$radio = SnmpQuery::cache()
    ->walk('ALBENTIA-AS-MIB::radioInfoTable')
    ->table(1);

foreach ($radio as $idx => $row) {
    $rssi = $row['ALBENTIA-AS-MIB::radioInfoTargetRSSI'] ?? null;
    if ($rssi === null || $rssi === '') {
        continue;
    }

    // Re-encode the string sectorColor index into SNMP numeric form so the
    // sensor_oid is pollable: "blue" -> "4.98.108.117.101"
    $idx_enc = (string) strlen((string) $idx);
    foreach (str_split((string) $idx) as $ch) {
        $idx_enc .= '.' . ord($ch);
    }

    discover_sensor(
        null, 'dbm', $device,
        '.1.3.6.1.4.1.28087.12.10.10.5.1.7.' . $idx_enc,
        'radioInfoTargetRSSI',
        'albentiaaos',
        'Target RSSI',
        1, 1, null, null, null, null,
        (int) $rssi
    );
    break;
}

unset($radio, $idx, $idx_enc, $ch, $row, $rssi);
