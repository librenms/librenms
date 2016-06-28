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

if ($device['os'] == 'junos' || $device['os_group'] == 'junos') {
    echo 'JunOS ';

    $multiplier = 1;
    $divisor    = 100;
    foreach ($junos_oids as $index => $entry) {
        if (is_numeric($entry['jnxDomCurrentRxLaserPower'])) {
            $oid = '.1.3.6.1.4.1.2636.3.60.1.1.1.1.5.'.$index;
            $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', array($index, $device['device_id'])) . ' Rx Power';
            $limit_low = $entry['jnxDomCurrentRxLaserPowerLowAlarmThreshold']/$divisor;
            $warn_limit_low = $entry['jnxDomCurrentRxLaserPowerLowWarningThreshold']/$divisor;
            $limit = $entry['jnxDomCurrentRxLaserPowerHighAlarmThreshold']/$divisor;
            $warn_limit = $entry['jnxDomCurrentRxLaserPowerHighWarningThreshold']/$divisor;
            $current = $entry['jnxDomCurrentRxLaserPower'];
            $entPhysicalIndex = $index;
            $entPhysicalIndex_measured = 'ports';
            discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'rx-'.$index, 'junos', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }

        if (is_numeric($entry['jnxDomCurrentTxLaserOutputPower'])) {
            $oid = '.1.3.6.1.4.1.2636.3.60.1.1.1.1.7.'.$index;
            $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', array($index, $device['device_id'])) . ' Tx Power';
            $limit_low = $entry['jnxDomCurrentTxLaserOutputPowerLowAlarmThreshold']/$divisor;
            $warn_limit_low = $entry['jnxDomCurrentTxLaserOutputPowerLowWarningThreshold']/$divisor;
            $limit = $entry['jnxDomCurrentModuleTemperatureHighAlarmThreshold']/$divisor;
            $warn_limit = $entry['jnxDomCurrentModuleTemperatureHighWarningThreshold']/$divisor;
            $current = $entry['jnxDomCurrentTxLaserOutputPower'];
            $entPhysicalIndex = $index;
            $entPhysicalIndex_measured = 'ports';
            discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'tx-'.$index, 'junos', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }

    }

}
