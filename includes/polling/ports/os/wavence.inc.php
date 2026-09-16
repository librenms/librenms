<?php

/**
 * LibreNMS
 *
 * Copyright (C) 2026 LibreNMS Contributors
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * See COPYING for more details.
 */

/*
 * Nokia Wavence reports IF-MIB::ifName and IF-MIB::ifAlias as the literal
 * string "NULL". Clear those values and allow port_fill_missing_and_trim()
 * to populate them from ifDescr.
 */
foreach ($port_stats as &$port) {
    if (($port['ifName'] ?? '') === 'NULL') {
        $port['ifName'] = '';
    }

    if (($port['ifAlias'] ?? '') === 'NULL') {
        $port['ifAlias'] = '';
    }
}

unset($port);

/*
 * Some Nokia Wavence releases/models return unusable standard IF-MIB octet
 * counters:
 *
 *   ifInOctets / ifOutOctets      = 0
 *   ifHCInOctets / ifHCOutOctets  = NULL
 *
 * Valid Ethernet PM counters are available from OPTICSIM-ETHPM-MIB.
 */
$wavence_oids = [
    'ethAggrMaintRxTRCO',
    'ethAggrMaintTxTTO',
    'ethAggrMaintRxTRSEF',
    'ethAggrMaintRxTDF',
    'ethAggrMaintTxTDF',
];

$wavence_pm = [];

foreach ($wavence_oids as $oid) {
    $data = SnmpQuery::hideMib()
        ->walk('OPTICSIM-ETHPM-MIB::' . $oid)
        ->table(1);

    foreach ($data as $ifIndex => $counters) {
        $wavence_pm[$ifIndex] = array_merge(
            $wavence_pm[$ifIndex] ?? [],
            $counters
        );
    }
}

if (! empty($wavence_pm)) {
    $fetched_data_string .= 'OPTICSIM-ETHPM-MIB::'
        . implode(' OPTICSIM-ETHPM-MIB::', $wavence_oids)
        . ' ';

    foreach ($port_stats as $ifIndex => &$port) {
        /*
         * Only apply Wavence Ethernet PM counters to Ethernet ports.
         * Do not apply them to the UBT-S Radio Channel interface.
         */
        if (($port['ifType'] ?? '') !== 'ethernetCsmacd') {
            continue;
        }

        $pm = $wavence_pm[$ifIndex] ?? [];

        if (is_numeric($pm['ethAggrMaintRxTRCO'] ?? null)) {
            $port['ifInOctets'] = $pm['ethAggrMaintRxTRCO'];
            $port['ifHCInOctets'] = $pm['ethAggrMaintRxTRCO'];
        }

        if (is_numeric($pm['ethAggrMaintTxTTO'] ?? null)) {
            $port['ifOutOctets'] = $pm['ethAggrMaintTxTTO'];
            $port['ifHCOutOctets'] = $pm['ethAggrMaintTxTTO'];
        }

        if (is_numeric($pm['ethAggrMaintRxTRSEF'] ?? null)) {
            $port['ifInErrors'] = $pm['ethAggrMaintRxTRSEF'];
        }

        if (is_numeric($pm['ethAggrMaintRxTDF'] ?? null)) {
            $port['ifInDiscards'] = $pm['ethAggrMaintRxTDF'];
        }

        if (is_numeric($pm['ethAggrMaintTxTDF'] ?? null)) {
            $port['ifOutDiscards'] = $pm['ethAggrMaintTxTDF'];
        }
    }

    unset($port);
}
