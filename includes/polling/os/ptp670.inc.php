<?php
/*
 * LibreNMS 
 *
 * Copyright (c) 2015 Christophe Martinet Chrisgfx <martinet.christophe@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/

$version = preg_replace("/.*Version (.*)$/", "\\1", $device['sysDescr']);
$data = snmp_get_multi($device, ['masterSlaveMode.0', 'hardwareVersion.0'], '-OQU', 'CAMBIUM-PTP670-MIB');

$masterSlaveMode = ucfirst($data[0]['CAMBIUM-PTP670-MIB::masterSlaveMode']);
$hwversion = ucfirst($data[0]['CAMBIUM-PTP670-MIB::hardwareVersion']);

$hardware = 'PTP 670 ' . $masterSlaveMode . "($hwversion)";
