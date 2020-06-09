<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2020 PipoCanaja@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$data = explode("_", $device['sysDescr'], 2);
$hardware = $data[0];
$version = $data[1];
$serial = snmp_get($device, 'XAVI-XG6846-MIB::serialnum.0', '-Oqv');
