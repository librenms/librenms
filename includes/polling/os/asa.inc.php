<?php

/*
 * LibreNMS Cisco wireless controller information module
 *
 * Copyright (c) 2016 Tuomas Riihimäki <tuomari@iudex.fi>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$oids = 'entPhysicalModelName.1 entPhysicalSoftwareRev.1 entPhysicalSerialNum.1 entPhysicalModelName.4 entPhysicalSoftwareRev.4';

$data = snmp_get_multi($device, $oids, '-OQUs', 'ENTITY-MIB');

if (isset($data[1]['entPhysicalSoftwareRev']) && $data[1]['entPhysicalSoftwareRev'] != '') {
    $version = $data[1]['entPhysicalSoftwareRev'];
}
else if (isset($data[4]['entPhysicalSoftwareRev']) && $data[4]['entPhysicalSoftwareRev'] != '') {
    $version = $data[4]['entPhysicalSoftwareRev'];
}

if (isset($data[1]['entPhysicalModelName']) && $data[1]['entPhysicalModelName'] != '') {
    $hardware = $data[1]['entPhysicalModelName'];
}
else if (isset($data[4]['entPhysicalSoftwareRev']) && $data[4]['entPhysicalSoftwareRev'] != '') {
    $hardware = $data[4]['entPhysicalModelName'];
}

if (isset($data[1]['entPhysicalSerialNum']) && $data[1]['entPhysicalSerialNum'] != '') {
    $serial = $data[1]['entPhysicalSerialNum'];
}

if (empty($hardware)) {
    $hardware = snmp_get($device, 'sysObjectID.0', '-Osqv', 'SNMPv2-MIB:CISCO-PRODUCTS-MIB');
}
