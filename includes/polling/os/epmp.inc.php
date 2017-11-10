<?php
/*
 * LibreNMS Pulse Secure OS information module
 *
 * Copyright (c) 2015 Christophe Martinet Chrisgfx <martinet.christophe@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/

$epmp_ap = snmp_get($device, 'wirelessInterfaceMode.0', '-Oqv', 'CAMBIUM-PMP80211-MIB');
$epmp_number = snmp_get($device, 'cambiumSubModeType.0', '-Oqv', 'CAMBIUM-PMP80211-MIB');

if ($epmp_ap == 1) {
    if ($epmp_number == 5) {
        $hardware = 'ePTP Master';
    } else {
        $hardware = 'ePMP AP';
    }
} elseif ($epmp_ap == 2) {
    if ($epmp_number == 4) {
        $hardware = 'ePTP Slave';
    } else {
        $hardware = 'ePMP SM';
    }
}

$version = snmp_get($device, 'cambiumCurrentuImageVersion.0', '-Oqv', 'CAMBIUM-PMP80211-MIB');
