<?php

/**
 * seh-uds.inc.php
 *
 * LibreNMS state discovery for SEH USB device servers (myUTN / dongleserver).
 *
 * The SEH utnPortTable mixes two indexings: the port configuration columns
 * (utnPortTag, ...) are indexed by the physical USB port, while the attached
 * USB device columns (utnPortUsbOwn, utnPortSlot, ...) are indexed by device
 * entry, with utnPortSlot pointing back to the physical port. Newer firmware
 * (sehMIB 2.x) moves the device columns to a separate utnDevTable.
 *
 * One sensor per attached device, named like the SEH web UI:
 * "Port 13: dongle13 - client11 (192.0.2.11)".
 * Numeric OIDs are used on purpose so this works regardless of MIB parsing.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @link       https://www.librenms.org
 */
$seh_walk = function (string $base): array {
    $out = [];
    foreach (\SnmpQuery::numeric()->walk($base)->values() as $oid => $val) {
        $out[substr($oid, strlen($base) + 1)] = trim((string) $val, "\" \t");
    }

    return $out;
};

// utnPortTag: indexed by physical port. firmware <= 20.1: sehPSrv.50.2.1.10, sehMIB 2.x: seh.5.10.2.1.10 (utnPortDescr = 12)
$seh_tag_by_port = [];
foreach (['.1.3.6.1.4.1.1229.2.50.2.1.10', '.1.3.6.1.4.1.1229.5.10.2.1.10', '.1.3.6.1.4.1.1229.5.10.2.1.12'] as $seh_tag_oid) {
    foreach ($seh_walk($seh_tag_oid) as $idx => $tag) {
        if ($tag !== '' && empty($seh_tag_by_port[(int) $idx])) {
            $seh_tag_by_port[(int) $idx] = $tag;
        }
    }
    if (! empty($seh_tag_by_port)) {
        break;
    }
}

// firmware <= 20.1: utnPortUsbOwn (col 26) / utnPortSlot (col 27) in utnPortTable
$seh_slot_oid = '.1.3.6.1.4.1.1229.2.50.2.1.27';
$seh_owns = $seh_walk('.1.3.6.1.4.1.1229.2.50.2.1.26');
$seh_slots = $seh_walk($seh_slot_oid);
if (empty($seh_owns)) {
    // sehMIB >= 2.x / firmware 20.2+: utnDevTable (utnDevOwn = 7, utnDevPort = 8)
    $seh_slot_oid = '.1.3.6.1.4.1.1229.5.20.2.1.8';
    $seh_owns = $seh_walk('.1.3.6.1.4.1.1229.5.20.2.1.7');
    $seh_slots = $seh_walk($seh_slot_oid);
}

$seh_state_name = 'utnPortUsbSlot';
$seh_states = [['value' => 0, 'generic' => 3, 'descr' => 'no device']];
for ($seh_i = 1; $seh_i <= 20; $seh_i++) {
    $seh_states[] = ['value' => $seh_i, 'generic' => 0, 'descr' => 'connected'];
}
create_state_index($seh_state_name, $seh_states);

foreach ($seh_owns as $index => $seh_own) {
    $seh_slot = (int) ($seh_slots[$index] ?? 0);
    if ($seh_own === '' || $seh_own === '-' || $seh_slot === 0) {
        continue; // empty device entry (no device, or placeholder without a physical port)
    }
    $seh_tag = $seh_tag_by_port[$seh_slot] ?? '';
    $seh_descr = 'Port ' . $seh_slot . ':' . ($seh_tag !== '' ? ' ' . $seh_tag : '') . ' - ' . $seh_own;

    discover_sensor(
        null,
        'state',
        $device,
        $seh_slot_oid . '.' . $index,
        'utnPortIndex.' . $index,
        $seh_state_name,
        $seh_descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $seh_slot,
        'snmp',
        null,
        null,
        null,
        'USB Port'
    );
}

unset($seh_walk, $seh_tag_by_port, $seh_tag_oid, $seh_slot_oid, $seh_owns, $seh_slots, $seh_state_name, $seh_states, $seh_i, $seh_own, $seh_slot, $seh_tag, $seh_descr, $index, $idx, $tag);
