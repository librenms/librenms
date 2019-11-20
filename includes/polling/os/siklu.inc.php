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

if (snmp_get($device, 'rbSwBank1Running.0', '-Osqnv', '+RADIO-BRIDGE-MIB') == 'running') {
    $version  = snmp_get($device, 'rbSwBank1Version.0', '-Osqnv', '+RADIO-BRIDGE-MIB');
} else {
    $version  = snmp_get($device, 'rbSwBank2Version.0', '-Osqnv', '+RADIO-BRIDGE-MIB');
}
$hardware = $device['sysDescr'];
$serial = snmp_get($device, 'entPhysicalSerialNum.1', '-Osqnv', '+ENTITY-MIB');
