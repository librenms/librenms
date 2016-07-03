<?php

/*
 * LibreNMS Accedian MetroNID OS Polling module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


$version = snmp_get($device, 'acdDescFirmwareVersion.0', '-Oqv', 'ACD-DESC-MIB');
$hardware = snmp_get($device, 'acdDescCommercialName.0', '-Ovqs', 'ACD-DESC-MIB');
$serial   = snmp_get($device, 'acdDescSerialNumber.0', '-OQv', 'ACD-DESC-MIB');
$features       = '';
