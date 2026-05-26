<?php

/*
 * Nokia Wavence reports IF-MIB::ifName and IF-MIB::ifAlias as the literal
 * string "NULL". This breaks device_bits because the default
 * device_traffic_descr filter includes /null/.
 *
 * Normalize ifName/ifAlias to ifDescr before the ports table is updated.
 */

foreach ($port_stats as $ifIndex => &$wavence_port) {
    $ifDescr = trim((string) ($wavence_port['ifDescr'] ?? ''));

    if ($ifDescr === '' || strtoupper($ifDescr) === 'NULL') {
        $ifDescr = 'Interface ' . $ifIndex;
    }

    $ifName = trim((string) ($wavence_port['ifName'] ?? ''));
    if ($ifName === '' || strtoupper($ifName) === 'NULL') {
        $wavence_port['ifName'] = $ifDescr;
    }

    $ifAlias = trim((string) ($wavence_port['ifAlias'] ?? ''));
    if ($ifAlias === '' || strtoupper($ifAlias) === 'NULL') {
        $wavence_port['ifAlias'] = $ifDescr;
    }
}

unset($wavence_port);
