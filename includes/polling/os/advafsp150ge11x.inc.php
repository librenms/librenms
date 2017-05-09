<?php
/*
 * LibreNMS - ADVA FSP150-CC-GE11x (MetroE Edge) device support
 *
 * Copyright (c) 2017 Christoph Zilian <czilian@hotmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$version  = 'SW V'.trim(snmp_get($device, "entPhysicalSoftwareRev.1", "-OQv", "ADVA-MIB"), '"');

$hardware = 'ADVA '.trim(snmp_get($device, "sysDescr.0", "-OQv", "ADVA-MIB"), '"')
            .' V'.trim(snmp_get($device, "entPhysicalHardwareRev.1", "-OQv", "ADVA-MIB"), '"');

$serial = trim(snmp_get($device, "entPhysicalSerialNum.1", "-OQv", "ADVA-MIB"), '"');

$features = ''; //search for PTP info in MIB
