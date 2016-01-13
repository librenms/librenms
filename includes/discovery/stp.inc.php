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

echo "Spanning Tree: ";

// Pre-cache existing state of STP for this device from database
$stp_db = dbFetchRow('SELECT * FROM `stp` WHERE `device_id` = ?', array($device['device_id']));
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
        if ($version[build] <= 16607) { // Buggy version :-(
            $tm = '1';
        }
    }

    // read the 802.1D subtree
    $stp_raw = snmpwalk_cache_oid($device, 'dot1dStp', array(), 'RSTP-MIB');
    $stp = array(
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
        'bridgeForwardDelay'      => $stp_raw[0]['dot1dStpBridgeForwardDelay'] * $tm
    );

    // set device binding
    $stp['device_id'] = $device['device_id'];

    // read the 802.1D bridge address and set as MAC in database
    $mac_raw = snmp_get($device, 'dot1dBaseBridgeAddress.0', '-Oqv', 'RSTP-MIB');
    
    // read Time as timetics (in hundredths of a seconds) since last topology change and convert to seconds
    $time_since_change = snmp_get($device, 'dot1dStpTimeSinceTopologyChange.0', '-Ovt', 'RSTP-MIB');
    if ($time_since_change > '100') {
        $time_since_change = substr($time_since_change, 0, -2); // convert to seconds since change
    }
    else {
        $time_since_change = '0';
    }
    $stp['timeSinceTopologyChange'] = $time_since_change;

    // designated root is stored in format 2 octet bridge priority + MAC address, so we need to normalize it 
    $dr = str_replace(array(' ', ':', '-'), '', strtolower($stp_raw[0]['dot1dStpDesignatedRoot']));
    $dr = substr($dr, -12); //remove first two octets
    $stp['designatedRoot'] = $dr;

    // normalize the MAC
    $mac_array = explode(':', $mac_raw);
    foreach($mac_array as &$octet) {
        if (strlen($octet) < 2) {
            $octet = "0" . $octet; // add suppressed 0
        }
    }
    $stp['bridgeAddress'] = implode($mac_array);

    // I'm the boss?
    if ($stp['bridgeAddress'] == $stp['designatedRoot']) {
        $stp['rootBridge'] = '1';
    }
    else {
        $stp['rootBridge'] = '0';
    }

    d_echo($stp);

    if ($stp_raw[0]['version'] == '3') {
        echo "RSTP ";
    }
    else { 
        echo "STP ";
    }

    if (!$stp_db['bridgeAddress'] && $stp['bridgeAddress']) {
        dbInsert($stp,'stp');
        log_event('STP added, bridge address: '.$stp['bridgeAddress'], $device, 'stp');
        echo '+';
    }
    
    if ($stp_db['bridgeAddress'] && !$stp['bridgeAddress']) {
        dbDelete('stp','device_id = ?', array($device['device_id']));
        log_event('STP removed', $device, 'stp');
        echo '-';
    }
}

unset($stp_raw, $stp, $stp_db);
echo "\n";
