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

$hardware = $device['sysDescr'];

$oidList = [
    '.1.3.6.1.4.1.890.1.5.8.20.1.10.0', //ZYXEL-GS4012F-MIB::sysSerialNumber.0
    '.1.3.6.1.4.1.890.1.5.8.47.1.10.0',// ZYXEL-MGS3712-MIB::sysSerialNumber.0
    '.1.3.6.1.4.1.890.1.5.8.55.1.10.0', //ZYXEL-GS2200-24-MIB::sysSerialNumber.0
];

foreach ($oidList as $oid) {
    $serial_tmp = snmp_get($device, $oid, '-OQv');
    if (!empty($serial_tmp)) {
        $serial = $serial_tmp;
        break;
    }
}
