<?php

/*
 * LibreNMS Interface Power dBm module for Brocade IronWare
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'Brocade IronWare Interface dBm ';

$multiplier = 1;
$divisor = 1000;

foreach ($pre_cache['ironware_optic_oids'] as $index => $entry) {
    if ($entry['snIfOpticalMonitoringRxPower']) {
        if (strpos($entry['snIfOpticalMonitoringRxPower'], 'N\/A') !== true) {
            return_num($entry);
            $oid = '.1.3.6.1.4.1.1991.1.1.3.3.6.1.3.' . $index;
            $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', [$index, $device['device_id']]) . ' Rx Power';
            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oid,
                'snIfOpticalMonitoringRxPower.'.$index,
                'ironware',
                $descr,
                null,
                null,
                null,
                null,
                null,
                null,
                $currentrx,
                'snmp'
            );
        }
    }

    if ($entry['snIfOpticalMonitoringTxPower']) {
        if (strpos($entry['snIfOpticalMonitoringTxPower'], 'N\/A') !== true) {
            return_num($entry);
            $oid = '.1.3.6.1.4.1.1991.1.1.3.3.6.1.2.' . $index;
            $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', [$index, $device['device_id']]) . ' Tx Power';
            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oid,
                'snIfOpticalMonitoringTxPower.'.$index,
                'ironware',
                $descr,
                null,
                null,
                null,
                null,
                null,
                null,
                $currenttx,
                'snmp'
            );
        }
    }
}
