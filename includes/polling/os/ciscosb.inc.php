<?php
/*
* LibreNMS Cisco Small Business OS information module
*
* Copyright (c) 2015 Mike Rostermund <mike@kollegienet.dk>
* This program is free software: you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation, either version 3 of the License, or (at your
* option) any later version.  Please see LICENSE.txt at the top level of
* the source code distribution for details.
*/
$version = trim(snmp_get($device, "SNMPv2-SMI::enterprises.9.6.1.101.2.4.0", "-Ovq") , '" ');
$hardware = trim(snmp_get($device, "SNMPv2-SMI::enterprises.9.6.1.101.53.14.1.11.1", "-Ovq") , '" ');
$serial = trim(snmp_get($device, "SNMPv2-SMI::enterprises.9.6.1.101.53.14.1.5.1", "-Ovq") , '" ');
$features = trim(snmp_get($device, "SNMPv2-SMI::enterprises.9.6.1.101.53.14.1.7.1", "-Ovq") , '" ');
?>
