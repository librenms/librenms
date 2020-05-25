<?php

/*
 * LibreNMS F5 TMM polling module
 *
 * Copyright (c) 2017 Paul Blasquez <pblasquez@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/


/*
/ F5-BIGIP-SYSTEM-MIB::sysStatMemoryTotal.0 = Counter64: 28638707712
/ F5-BIGIP-SYSTEM-MIB::sysStatMemoryUsed.0 = Counter64: 1794195576
*/


$mempool['used']  = snmp_get($device, 'sysGlobalStat.sysStatMemoryUsed.0', '-OvQ', 'F5-BIGIP-SYSTEM-MIB');
$mempool['total'] = snmp_get($device, 'sysGlobalStat.sysStatMemoryTotal.0', '-OvQ', 'F5-BIGIP-SYSTEM-MIB');
$mempool['free']  = ($mempool['total'] - $mempool['used']);
$mempool['perc']  = ($mempool['used'] / $mempool['total']) * 100;
