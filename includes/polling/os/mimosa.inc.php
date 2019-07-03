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

$hardware = snmp_get($device, 'sysObjectID.0', '-Osqv', 'SNMPv2-MIB:MIMOSA-NETWORKS-BASE-MIB');
$oids = ['mimosaSerialNumber.0', 'mimosaFirmwareVersion.0'];
$data = snmp_get_multi($device, $oids, '-OQUs', 'MIMOSA-NETWORKS-BFIVE-MIB');

$serial = $data[0]['mimosaSerialNumber'];
$version = $data[0]['mimosaFirmwareVersion'];
