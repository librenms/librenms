<?php

/**
 * seh-uds.inc.php
 *
 * Pre-cache for SEH USB device servers (myUTN / dongleserver / utnserver).
 *
 * The attached USB devices and the physical ports live in rows with different
 * indexes: the device row (owner, physical port) is indexed by device entry,
 * the port row (utnPortTag) by physical port. utnPortSlot / utnDevPort points
 * from the device entry to the port. YAML discovery can only look up columns
 * of the current row, so the join is done here and the joined rows are
 * pre-populated under the table names seh-uds.yaml uses as oid (one row per
 * attached device: seh_usb_slot, seh_usb_tag, seh_usb_own, seh_usb_label):
 *
 *  - SEH-PSRV-MIB::utnPortTable: SEH-PSRV-MIB 1.x, everything in utnPortTable
 *        (sehPSrv.50.2.1: tag = 10, utnPortUsbOwn = 26, utnPortSlot = 27)
 *  - SEH-PSRV-MIB::utnDevTable:  sehMIB 2.x, utnPortTable (seh.5.10.2.1: tag = 10, descr = 12)
 *        + utnDevTable (seh.5.20.2.1: utnDevOwn = 7, utnDevPort = 8)
 *
 * Numeric OIDs on purpose: both MIB versions share the module name
 * SEH-PSRV-MIB and each lacks the objects of the other layout.
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

$seh_join = function (array $tags_by_port, array $owns, array $slots): array {
    $rows = [];
    foreach ($owns as $index => $own) {
        $slot = (int) ($slots[$index] ?? 0);
        if ($own === '' || $own === '-' || $slot === 0) {
            continue; // empty device entry
        }
        $tag = $tags_by_port[$slot] ?? '';
        $rows[$index] = [
            'seh_usb_slot' => $slot,
            'seh_usb_tag' => $tag,
            'seh_usb_own' => $own,
            'seh_usb_label' => ($tag !== '' ? $tag . ' - ' : '') . $own,
        ];
    }

    return $rows;
};

// always define both keys so YamlDiscovery::preCache() does not try to walk them
$pre_cache['SEH-PSRV-MIB::utnPortTable'] = [];
$pre_cache['SEH-PSRV-MIB::utnDevTable'] = [];

$seh_owns = $seh_walk('.1.3.6.1.4.1.1229.2.50.2.1.26');
if (! empty($seh_owns)) {
    $seh_tags = [];
    foreach ($seh_walk('.1.3.6.1.4.1.1229.2.50.2.1.10') as $idx => $tag) {
        $seh_tags[(int) $idx] = $tag; // index is <port>.0
    }
    $pre_cache['SEH-PSRV-MIB::utnPortTable'] = $seh_join($seh_tags, $seh_owns, $seh_walk('.1.3.6.1.4.1.1229.2.50.2.1.27'));
} else {
    $seh_owns = $seh_walk('.1.3.6.1.4.1.1229.5.20.2.1.7');
    if (! empty($seh_owns)) {
        $seh_tags = [];
        foreach ($seh_walk('.1.3.6.1.4.1.1229.5.10.2.1.10') as $idx => $tag) {
            $seh_tags[(int) $idx] = $tag;
        }
        foreach ($seh_walk('.1.3.6.1.4.1.1229.5.10.2.1.12') as $idx => $descr) {
            if (empty($seh_tags[(int) $idx]) && $descr !== '') {
                $seh_tags[(int) $idx] = $descr; // fall back to utnPortDescr when the tag is unset
            }
        }
        $pre_cache['SEH-PSRV-MIB::utnDevTable'] = $seh_join($seh_tags, $seh_owns, $seh_walk('.1.3.6.1.4.1.1229.5.20.2.1.8'));
    }
}

unset($seh_walk, $seh_join, $seh_owns, $seh_tags, $idx, $tag, $descr);
