<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Vitali Kari <vitali.kari@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * Based on IEEE-802.1D-2004, (STP, RSTP)
 * needs RSTP-MIB
 */

// Pre-cache existing state of STP for this device from database
$stp_db = dbFetchRow('SELECT * FROM `stp` WHERE `device_id` = ?', [$device['device_id']]);
//d_echo($stp_db);

$stpprotocol = snmp_get($device, 'dot1dStpProtocolSpecification.0', '-Oqv', 'RSTP-MIB');

// FIXME I don't know what "unknown" means, perhaps MSTP? (saw it on some cisco devices)
// But we can try to retrieve data
if ($stpprotocol == 'ieee8021d' || $stpprotocol == 'unknown') {
    // set time multiplier to convert from centiseconds to seconds
    // all time values are stored in databese as seconds
    $tm = '0.01';
    // some vendors like PBN dont follow the 802.1D implementation and use seconds in SNMP
    if ($device['os'] == 'pbn') {
        preg_match('/^.* Build (?<build>\d+)/', $device['version'], $version);
        if ($version['build'] <= 16607) { // Buggy version :-(
            $tm = '1';
        }
    }

    // read the 802.1D subtree
    $stp_raw = snmpwalk_cache_oid($device, 'dot1dStp', [], 'RSTP-MIB');
    $stp = [
        'protocolSpecification'   => $stp_raw[0]['dot1dStpProtocolSpecification'],
        'priority'                => $stp_raw[0]['dot1dStpPriority'],
        'topChanges'              => $stp_raw[0]['dot1dStpTopChanges'],
        'rootCost'                => $stp_raw[0]['dot1dStpRootCost'],
        'rootPort'                => $stp_raw[0]['dot1dStpRootPort'],
        'maxAge'                  => $stp_raw[0]['dot1dStpMaxAge'] * $tm,
        'helloTime'               => $stp_raw[0]['dot1dStpHelloTime'] * $tm,
        'holdTime'                => $stp_raw[0]['dot1dStpHoldTime'] * $tm,
        'forwardDelay'            => $stp_raw[0]['dot1dStpForwardDelay'] * $tm,
        'bridgeMaxAge'            => $stp_raw[0]['dot1dStpBridgeMaxAge'] * $tm,
        'bridgeHelloTime'         => $stp_raw[0]['dot1dStpBridgeHelloTime'] * $tm,
        'bridgeForwardDelay'      => $stp_raw[0]['dot1dStpBridgeForwardDelay'] * $tm,
    ];

    // set device binding
    $stp['device_id'] = $device['device_id'];

    // read the 802.1D bridge address and set as MAC in database
    $mac_raw = snmp_get($device, 'dot1dBaseBridgeAddress.0', '-Oqv', 'RSTP-MIB');

    // read Time as timetics (in hundredths of a seconds) since last topology change and convert to seconds
    $time_since_change = snmp_get($device, 'dot1dStpTimeSinceTopologyChange.0', '-Ovt', 'RSTP-MIB');
    if ($time_since_change > '100') {
        $time_since_change = substr($time_since_change, 0, -2); // convert to seconds since change
    } else {
        $time_since_change = '0';
    }
    $stp['timeSinceTopologyChange'] = $time_since_change;

    // designated root is stored in format 2 octet bridge priority + MAC address, so we need to normalize it
    $dr = str_replace([' ', ':', '-'], '', strtolower($stp_raw[0]['dot1dStpDesignatedRoot']));
    $dr = substr($dr, -12); //remove first two octets
    $stp['designatedRoot'] = $dr;

    // normalize the MAC
    $mac_array = explode(':', $mac_raw);
    foreach ($mac_array as &$octet) {
        if (strlen($octet) < 2) {
            $octet = '0' . $octet; // add suppressed 0
        }
    }
    $stp['bridgeAddress'] = implode($mac_array);

    // I'm the boss?
    if ($stp['bridgeAddress'] == $stp['designatedRoot']) {
        $stp['rootBridge'] = '1';
    } else {
        $stp['rootBridge'] = '0';
    }

    d_echo($stp);

    if ($stp_raw[0]['version'] == '3') {
        echo 'RSTP ';
    } else {
        echo 'STP ';
    }

    if (! $stp_db['bridgeAddress'] && $stp['bridgeAddress']) {
        dbInsert($stp, 'stp');
        log_event('STP added, bridge address: ' . $stp['bridgeAddress'], $device, 'stp', 3);
        echo '+';
    }

    if ($stp_db['bridgeAddress'] && ! $stp['bridgeAddress']) {
        dbDelete('stp', 'device_id = ?', [$device['device_id']]);
        log_event('STP removed', $device, 'stp', 4);
        echo '-';
    }

    // STP port related stuff
    foreach ($stp_raw as $port => $value) {
        if ($port) { // $stp_raw[0] ist not port related so we skip this one
            $stp_port = [
                'priority'              => $stp_raw[$port]['dot1dStpPortPriority'],
                'state'                 => $stp_raw[$port]['dot1dStpPortState'],
                'enable'                => $stp_raw[$port]['dot1dStpPortEnable'],
                'pathCost'              => $stp_raw[$port]['dot1dStpPortPathCost'],
                'designatedCost'        => $stp_raw[$port]['dot1dStpPortDesignatedCost'],
                'designatedPort'        => $stp_raw[$port]['dot1dStpPortDesignatedPort'],
                'forwardTransitions'    => $stp_raw[$port]['dot1dStpPortForwardTransitions'],
            ];

            // set device binding
            $stp_port['device_id'] = $device['device_id'];

            // set port binding
            $stp_port['port_id'] = dbFetchCell('SELECT port_id FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?', [$device['device_id'], $stp_raw[$port]['dot1dStpPort']]);

            $dr = str_replace([' ', ':', '-'], '', strtolower($stp_raw[$port]['dot1dStpPortDesignatedRoot']));
            $dr = substr($dr, -12); //remove first two octets
            $stp_port['designatedRoot'] = $dr;

            $db = str_replace([' ', ':', '-'], '', strtolower($stp_raw[$port]['dot1dStpPortDesignatedBridge']));
            $db = substr($db, -12); //remove first two octets
            $stp_port['designatedBridge'] = $db;

            if ($device['os'] == 'pbn') {
                // It seems that PBN guys don't care about ieee 802.1d :-(
                // So try to find the right port with some crazy conversations
                $dp_value = dechex($stp_port['priority']);
                $dp_value = $dp_value . '00';
                $dp_value = hexdec($dp_value);
                if ($stp_raw[$port]['dot1dStpPortDesignatedPort']) {
                    $dp = $stp_raw[$port]['dot1dStpPortDesignatedPort'] - $dp_value;
                    $stp_port['designatedPort'] = $dp;
                }
            } else {
                // Port saved in format priority+port (ieee 802.1d-1998: clause 8.5.5.1)
                $dp = substr($stp_raw[$port]['dot1dStpPortDesignatedPort'], -2); //discard the first octet (priority part)
                $stp_port['designatedPort'] = hexdec($dp);
            }

            d_echo($stp_port);

            // Write to db
            if (! dbFetchCell('SELECT 1 FROM `ports_stp` WHERE `device_id` = ? AND `port_id` = ?', [$device['device_id'], $stp_port['port_id']])) {
                dbInsert($stp_port, 'ports_stp');
                echo '+';
            }
        }
    }

    // Delete STP ports from db if absent in SNMP query
    $stp_port_db = dbFetchRows('SELECT * FROM `ports_stp` WHERE `device_id` = ?', [$device['device_id']]);
    //d_echo($stp_port_db);
    foreach ($stp_port_db as $port => $value) {
        $if_index = dbFetchCell('SELECT `ifIndex` FROM `ports` WHERE `device_id` = ? AND `port_id` = ?', [$device['device_id'], $value['port_id']]);
        if ($if_index != $stp_raw[$if_index]['dot1dStpPort']) {
            dbDelete('ports_stp', '`device_id` = ? AND `port_id` = ?', [$device['device_id'], $value['port_id']]);
            echo '-';
        }
    }
}

unset($stp_raw, $stp, $stp_db, $stp_port, $stp_port_db);
echo "\n";
