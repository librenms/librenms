<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


$oids = ['entPhysicalSoftwareRev.22', 'entPhysicalName.149', 'entPhysicalSerialNum.10'];

$data = snmp_get_multi($device, $oids, '-OQUs', 'ENTITY-MIB');

if (isset($data[22]['entPhysicalSoftwareRev']) && !empty($data[22]['entPhysicalSoftwareRev'])) {
    $version = $data[22]['entPhysicalSoftwareRev'];
}

if (isset($data[149]['entPhysicalName']) && !empty($data[149]['entPhysicalName'])) {
    $hardware = str_replace(' Chassis', '', $data[149]['entPhysicalName']);
}

if (isset($data[10]) && !empty($data[10]['entPhysicalSerialNum'])) {
    $serial = $data[10]['entPhysicalSerialNum'];
}
