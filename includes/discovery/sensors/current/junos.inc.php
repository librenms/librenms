<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Neil Lathwood <neil@lathwood.co.uk>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'JunOS ';

$multiplier = 1;
$divisor = 1000000;
foreach ($pre_cache['junos_oids'] as $index => $entry) {
    if (is_numeric($entry['jnxDomCurrentTxLaserBiasCurrent']) && $entry['jnxDomCurrentTxLaserBiasCurrent'] != 0 && $entry['jnxDomCurrentTxLaserBiasCurrentLowAlarmThreshold'] != 0) {
        $oid = '.1.3.6.1.4.1.2636.3.60.1.1.1.1.6.' . $index;
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', [$index, $device['device_id']]) . ' Tx Current';
        $limit_low = $entry['jnxDomCurrentTxLaserBiasCurrentLowAlarmThreshold'] / $divisor;
        $warn_limit_low = $entry['jnxDomCurrentTxLaserBiasCurrentLowWarningThreshold'] / $divisor;
        $limit = $entry['jnxDomCurrentTxLaserBiasCurrentHighAlarmThreshold'] / $divisor;
        $warn_limit = $entry['jnxDomCurrentTxLaserBiasCurrentHighWarningThreshold'] / $divisor;
        $current = $entry['jnxDomCurrentTxLaserBiasCurrent'];
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor($valid['sensor'], 'current', $device, $oid, 'rx-' . $index, 'junos', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
