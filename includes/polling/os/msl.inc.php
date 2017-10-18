<?php

/*
 * LibreNMS OS Polling module for Mitel Standard Linux
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// SNMPv2-MIB::sysDescr.0 = STRING: VerAg:05.05.57.00.01;VerHw:x86_64;VerSw:10.05.19.00.00
// Use preg_replace to strip out everything but the VerHW value, e.g. "x86_64"
$mslhw = snmp_get($device, "sysDescr.0", "-OQv", "SNMPv2-MIB");
$hardware = preg_replace('/;VerSw.*$/', '', (preg_replace('/^.*VerHw:/', '', $mslhw)));
// MITEL-APPCMN-MIB::mitelAppTblProductName.10.1.3.6.1.4.1.1027.1.6.1 = STRING: Mitel Standard Linux
// MITEL-APPCMN-MIB::mitelAppTblProductVersion.10.1.3.6.1.4.1.1027.1.6.1 = STRING: 10.5.19.0
$version = snmp_get($device, "mitelAppTblProductVersion.10.1.3.6.1.4.1.1027.1.6.1", "-OQv", "MITEL-APPCMN-MIB");
// MITEL-APPCMN-MIB::mitelAppTblProductDescr.10.1.3.6.1.4.1.1027.1.6.1 = STRING: Mitel Linux distribution platform to host Mitel solutions.
$features = snmp_get($device, "mitelAppTblProductDescr.10.1.3.6.1.4.1.1027.1.6.1", "-OQv", "MITEL-APPCMN-MIB");
