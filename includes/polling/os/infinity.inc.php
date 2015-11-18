<?php
/*
 * LibreNMS LigoWave Infinity OS information module
 *
 * Copyright (c) 2015 Mike Rostermund <mike@kollegienet.dk>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$version  = snmp_get($device, 'SNMPv2-MIB::sysDescr.0', '-Ovq');
$version  = explode(' ', $version)[2];
$hardware = snmp_get($device, 'IEEE802dot11-MIB::dot11manufacturerProductName.5', '-Ovq');
