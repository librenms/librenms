<?php

/*
 * LibreNMS Interface Power dBm module for the MRV® OptiDriver® Optical Transport Platform
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'MRV OptiDriver ';

$multiplier = 1;
$divisor = 1000;

foreach ($pre_cache['mrv-od_port-table'] as $index => $entry) {
    if ($entry['nbsCmmcPortRxPower']) {
        $oid = '.1.3.6.1.4.1.629.200.8.1.1.32.' . $index;
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifName`= ? AND `device_id` = ?', array($index, $device['device_id'])) . ' Rx Power';
        $currentrx = $entry['nbsCmmcPortRxPower'];
        discover_sensor(
            $valid['sensor'],
            'dbm',
            $device,
            $oid,
            'nbsCmmcPortRxPower.'.$index,
            'mrv-od',
            $descr,
            $divisor,
            $multiplier,
            null,
            null,
            null,
            null,
            $currentrx,
            'snmp'
        );
    }

    if ($entry['nbsCmmcPortTxPower']) {
        $oid = '.1.3.6.1.4.1.629.200.8.1.1.31.' . $index;
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifName`= ? AND `device_id` = ?', array($index, $device['device_id'])) . ' Tx Power';
        $currenttx = $entry['nbsCmmcPortTxPower'];
        discover_sensor(
            $valid['sensor'],
            'dbm',
            $device,
            $oid,
            'nbsCmmcPortTxPower.'.$index,
            'mrv-od',
            $descr,
            $divisor,
            $multiplier,
            null,
            null,
            null,
            null,
            $currenttx,
            'snmp'
        );
    }
}
