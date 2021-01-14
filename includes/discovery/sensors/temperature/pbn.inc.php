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
$divisor = 256;
foreach ($pre_cache['pbn_oids'] as $index => $entry) {
    if (is_numeric($entry['temperature']) && ($entry['temperature'] !== '-65535')) {
        $oid = '.1.3.6.1.4.1.11606.10.9.63.1.7.1.4.' . $index;
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', [$index, $device['device_id']]) . ' Temperature';
        $limit_low = -256;
        $warn_limit_low = 10;
        $limit = 256;
        $warn_limit = 80;
        $value = $entry['temperature'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, '' . $index, 'pbn', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
