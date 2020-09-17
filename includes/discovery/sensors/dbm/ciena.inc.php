<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * LibreNMS
 * @link       http://librenms.org
 * @author     Adam Kujawski <adamkuj@amplex.net>
 */

echo 'Ciena ';

$multiplier = 1;
$divisor    = 10000;
foreach ($pre_cache['ciena_oids'] as $index => $entry) {
    if (is_numeric($entry['wwpLeosPortXcvrRxDbmPower']) &&
    $entry['wwpLeosPortXcvrRxDbmPower'] != 0 &&
    $entry['wwpLeosPortXcvrTxDbmPower'] != 0) {
        $oid = '.1.3.6.1.4.1.6141.2.60.4.1.1.1.1.105.'.$index;
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifName`= ? AND `device_id` = ?',
            array($index, $device['device_id'])) . ' Rx Power';
        $limit_low = $entry['wwpLeosPortXcvrLowRxDbmPwAlarmThreshold']/$divisor;
        $limit = $entry['wwpLeosPortXcvrHighRxDbmPwAlarmThreshold']/$divisor;
        $current = $entry['wwpLeosPortXcvrRxDbmPower'];
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'rx-'.$index, 'ciena-sds',
            $descr, $divisor, $multiplier, $limit_low, null, null, $limit, $current, 'snmp',
            $entPhysicalIndex, $entPhysicalIndex_measured);
    }

    if (is_numeric($entry['wwpLeosPortXcvrTxDbmPower']) &&
    $entry['wwpLeosPortXcvrTxDbmPower'] &&
    $entry['wwpLeosPortXcvrRxDbmPower']) {
        $oid = '.1.3.6.1.4.1.6141.2.60.4.1.1.1.1.106.'.$index;
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifName`= ? AND `device_id` = ?',
            array($index, $device['device_id'])) . ' Tx Power';
        $limit_low = $entry['wwpLeosPortXcvrLowTxDbmPwAlarmThreshold']/$divisor;
        $limit = $entry['wwpLeosPortXcvrHighTxDbmPwAlarmThreshold']/$divisor;
        $current = $entry['wwpLeosPortXcvrTxDbmPower'];
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'tx-'.$index, 'ciena-sds',
            $descr, $divisor, $multiplier, $limit_low, null, null, $limit, $current, 'snmp',
            $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
