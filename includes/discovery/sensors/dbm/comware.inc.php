<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com> 
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
 
if ($device['os'] == 'comware') {
    echo 'Comware ';
    
    // Based on 10G_BASE_LR_SFP, as HP does not provide threshold values through snmp
    // Alarm thresholds:
    // RX power(dBm)  TX power(dBm)
    // 2.50           3.50
    // -12.30         -11.20
    
    $multiplier = 1;
    $divisor    = 100;
    foreach ($comware_oids as $index => $entry) {
        if (is_numeric($entry['hh3cTransceiverCurRXPower']) && $entry['hh3cTransceiverCurRXPower'] != 2147483647) {
            $oid                       = '.1.3.6.1.4.1.25506.2.70.1.1.1.12.' . $index;
            $dbquery                   = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", array(
                $index,
                $device['device_id']
            ));
            $limit_low                 = -30;
            $warn_limit_low            = -12.3;
            $limit                     = 2.5;
            $warn_limit                = -3;
            $current                   = $entry['hh3cTransceiverCurRXPower'] / $divisor;
            $entPhysicalIndex          = $index;
            $entPhysicalIndex_measured = 'ports';
            foreach ($dbquery as $dbindex => $dbresult) {
                $descr = $dbresult['ifDescr'] . ' Rx Power';
                discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'rx-' . $index, 'comware', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
            }
        }
        
        if (is_numeric($entry['hh3cTransceiverCurTXPower']) && $entry['hh3cTransceiverCurTXPower'] != 2147483647) {
            $oid                       = '.1.3.6.1.4.1.25506.2.70.1.1.1.9.' . $index;
            $dbquery                   = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", array(
                $index,
                $device['device_id']
            ));
            $limit_low                 = -30;
            $warn_limit_low            = -11.2;
            $limit                     = 3.5;
            $warn_limit                = -3;
            $current                   = $entry['hh3cTransceiverCurTXPower'] / $divisor;
            $entPhysicalIndex          = $index;
            $entPhysicalIndex_measured = 'ports';
            foreach ($dbquery as $dbindex => $dbresult) {
                $descr = $dbresult['ifDescr'] . ' Tx Power';
                discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'tx-' . $index, 'comware', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
            }
        }
    }
}
