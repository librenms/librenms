<?php

/*
 * E5-1xx portion of LibreNMS Calix OS Polling module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */



if (strstr($sysObjectId, '.6321.1.2.3')) { // E5-1xx Series

    if (strpos($device['sysObjectID'], 'enterprises.6321.1.2.3.4') !== false) { // E5-121

        $version = snmp_get($device, 'iesSlotModuleFWVersion.0.0', '-Oqv', '+E5-121-IESCOMMON-MIB', '+'.$config['install_dir'].'/mibs/calix');
        $hardware = snmp_get($device, 'iesSlotModuleDescr.0.0', '-Ovqs', '+E5-121-IESCOMMON-MIB', '+'.$config['install_dir'].'/mibs/calix');
        $serial   = snmp_get($device, 'iesChassisSerialNumber.0', '-OQv', '+E5-121-IESCOMMON-MIB', '+'.$config['install_dir'].'/mibs/calix');
        $features       = '';

        $version = str_replace('"', '', $version);
        $serial = str_replace('"', '', $serial);
        $hardware = str_replace('"', '', $hardware);

    }

    if (strpos($device['sysObjectID'], 'enterprises.6321.1.2.3.3') !== false) { // E5-120

        $version = snmp_get($device, 'iesSlotModuleFWVersion.0.0', '-Oqv', '+E5-120-IESCOMMON-MIB', '+'.$config['install_dir'].'/mibs/calix');
        $hardware = snmp_get($device, 'iesSlotModuleDescr.0.0', '-Ovqs', '+E5-120-IESCOMMON-MIB', '+'.$config['install_dir'].'/mibs/calix');
        $serial   = snmp_get($device, 'iesChassisSerialNumber.0', '-OQv', '+E5-120-IESCOMMON-MIB', '+'.$config['install_dir'].'/mibs/calix');
        $features       = '';

        $version = str_replace('"', '', $version);
        $serial = str_replace('"', '', $serial);
        $hardware = str_replace('"', '', $hardware);

    }

    if (strpos($device['sysObjectID'], 'enterprises.6321.1.2.3.2') !== false) { // E5-111

        $version = snmp_get($device, 'iesSlotModuleFWVersion.0.0', '-Oqv', '+E5-111-IESCOMMON-MIB', '+'.$config['install_dir'].'/mibs/calix');
        $hardware = snmp_get($device, 'iesSlotModuleDescr.0.0', '-Ovqs', '+E5-111-IESCOMMON-MIB', '+'.$config['install_dir'].'/mibs/calix');
        $serial   = snmp_get($device, 'iesChassisSerialNumber.0', '-OQv', '+E5-111-IESCOMMON-MIB', '+'.$config['install_dir'].'/mibs/calix');
        $features       = '';

        $version = str_replace('"', '', $version);
        $serial = str_replace('"', '', $serial);
        $hardware = str_replace('"', '', $hardware);

    }

    if (strpos($device['sysObjectID'], 'enterprises.6321.1.2.3.1') !== false) { // E5-110

        $version = snmp_get($device, 'iesSlotModuleFWVersion.0.0', '-Oqv', '+E5-110-IESCOMMON-MIB', '+'.$config['install_dir'].'/mibs/calix');
        $hardware = snmp_get($device, 'iesSlotModuleDescr.0.0', '-Ovqs', '+E5-110-IESCOMMON-MIB', '+'.$config['install_dir'].'/mibs/calix');
        $serial   = snmp_get($device, 'iesChassisSerialNumber.0', '-OQv', '+E5-110-IESCOMMON-MIB', '+'.$config['install_dir'].'/mibs/calix');
        $features       = '';

        $version = str_replace('"', '', $version);
        $serial = str_replace('"', '', $serial);
        $hardware = str_replace('"', '', $hardware);
    }
}

// Device might not have a card 1 (or even card2 if it is an E7-20)
$version = strtok(snmp_walk($device, "e7CardSoftwareVersion.1", "-OQv", "E7-Calix-MIB"), PHP_EOL);
$hardware = "Calix " . $poll_device['sysDescr'];
$features = str_replace(PHP_EOL, ', ', snmp_walk($device, "e7CardProvType", "-OQv", "E7-Calix-MIB"));
$serial = str_replace(PHP_EOL, ', ', snmp_walk($device, "e7CardSerialNumber", "-OQv", "E7-Calix-MIB"));
