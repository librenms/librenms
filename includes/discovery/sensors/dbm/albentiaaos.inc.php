<?php

/**
 * Albentia: Target RSSI (config setpoint, single global value per BS).
 *
 * All sectors return the same value, so publish only the first row.
 * Not exposed in Wireless > RSSI on purpose — that dashboard is for measured
 * signal, not configuration targets.
 *
 * @var \LibreNMS\OS\Albentiaaos $os  Provided by sensors() in
 *                              includes/discovery/functions.inc.php
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
    discover_sensor(
        null, 'dbm', $device,
        '.1.3.6.1.4.1.28087.12.10.10.5.1.7.' . $os->encodeStringIndex((string) $idx),
        'radioInfoTargetRSSI',
        'albentiaaos',
        'Target RSSI',
        1, 1, null, null, null, null,
        (int) $rssi
    );
    break;
}

unset($radio, $idx, $row, $rssi);
