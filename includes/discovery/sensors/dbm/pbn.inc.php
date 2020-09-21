<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'PBN ';

$multiplier = 1;
$divisor = 1;
foreach ($pre_cache['pbn_oids'] as $index => $entry) {
    if (is_numeric($entry['rxPower']) && ($entry['rxPower'] !== '-65535')) {
        $oid = '.1.3.6.1.4.1.11606.10.9.63.1.7.1.3.' . $index;
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', [$index, $device['device_id']]) . ' Rx Power';
        $limit_low = -30 / $divisor;
        $warn_limit_low = -25 / $divisor;
        $limit = -2 / $divisor;
        $warn_limit = -3 / $divisor;
        $value = $entry['rxPower'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'rx-' . $index, 'pbn', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }

    if (is_numeric($entry['txPower']) && ($entry['txPower'] !== '-65535')) {
        $oid = '.1.3.6.1.4.1.11606.10.9.63.1.7.1.2.' . $index;
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', [$index, $device['device_id']]) . ' Tx Power';
        $limit_low = -30 / $divisor;
        $warn_limit_low = -25 / $divisor;
        $limit = -2 / $divisor;
        $warn_limit = -3 / $divisor;
        $value = $entry['txPower'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'tx-' . $index, 'pbn', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
