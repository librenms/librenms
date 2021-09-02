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

$version = $device['sysDescr'];
$masterSlaveMode = ucfirst(snmp_get($device, 'masterSlaveMode.0', '-Oqv', 'CAMBIUM-PTP250-MIB'));
$hardware = 'PTP 250 ' . $masterSlaveMode;
