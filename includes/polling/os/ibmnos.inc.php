<?php
/*
 * LibreNMS IBM NOS information module
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
$sysdescr_value = $device['sysDescr'];
if (strpos($sysdescr_value, 'IBM Networking Operating System') !== false) {
    $hardware = str_replace('IBM Networking Operating System', '', $sysdescr_value);
    if (strpos($sysdescr_value, 'G8052') !== false) {
        $version = trim(snmp_get($device, '.1.3.6.1.2.1.47.1.1.1.1.10.1', '-Ovq'), '" ');
        $serial = trim(snmp_get($device, '.1.3.6.1.4.1.26543.100.100.14.9.0', '-Ovq'), '" ');
    }

    if (strpos($sysdescr_value, 'G8316') !== false) {
        $version = trim(snmp_get($device, '.1.3.6.1.2.1.47.1.1.1.1.10.1', '-Ovq'), '" ');
        $serial = trim(snmp_get($device, '.1.3.6.1.4.1.20301.100.100.14.9.0', '-Ovq'), '" ');
    }

    if (strpos($sysdescr_value, 'G8264CS') !== false) {
        $version = trim(snmp_get($device, '.1.3.6.1.4.1.20301.2.7.15.1.1.1.10.0', '-Ovq'), '" ');
        $serial = trim(snmp_get($device, '.1.3.6.1.4.1.20301.100.100.14.9.0', '-Ovq'), '" ');
    }

    if (strpos($sysdescr_value, 'G8264-T') !== false) {
        $version = trim(snmp_get($device, '.1.3.6.1.4.1.20301.2.7.13.1.1.1.10.0', '-Ovq'), '" ');
        $serial = trim(snmp_get($device, '.1.3.6.1.4.1.20301.100.100.14.9.0', '-Ovq'), '" ');
    }

    if (empty($version)) {
        $version = trim(snmp_get($device, '.1.3.6.1.2.1.47.1.1.1.1.10.1', '-Ovq'), '" ');
    }

    if (empty($serial)) {
        $serial = trim(snmp_get($device, '.1.3.6.1.2.1.47.1.1.1.1.11.1', '-Ovq'), '" ');
    }
} elseif (strpos($sysdescr_value, 'IBM Flex System Fabric') !== false) {
    $hardware = str_replace('IBM Flex System Fabric', '', $sysdescr_value);
    $version = trim(snmp_get($device, '.1.3.6.1.2.1.47.1.1.1.1.10.1', '-Ovq'), '" ');
    $serial = trim(snmp_get($device, '.1.3.6.1.2.1.47.1.1.1.1.11.1', '-Ovq'), '" ');
} elseif (strpos($sysdescr_value, 'IBM Networking OS 1/10Gb Uplink Ethernet Switch Module') !== false) {
    $hardware = 'IBM BladeCenter 1/10Gb Uplink Ethernet Switch Module';
}//end if
