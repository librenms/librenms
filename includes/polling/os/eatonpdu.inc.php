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

$hardware = trim(snmp_get($device, 'partNumber.0', '-Ovq', 'EATON-EPDU-MIB'), '"');
$hardware .= trim(snmp_get($device, 'objectName.0', '-Ovq', 'PDU-MIB'), '"');
$version = trim(snmp_get($device, 'firmwareVersion.0', '-Ovq', 'EATON-EPDU-MIB:PDU-MIB'), '"');
$serial = trim(snmp_get($device, 'serialNumber.0', '-Ovq', 'EATON-EPDU-MIB:PDU-MIB'), '"');
