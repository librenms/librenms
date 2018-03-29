<?php
/*
 * LibreNMS LigoWave Infinity memory information module
 *
 * Copyright (c) 2015 Mike Rostermund <mike@kollegienet.dk>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$total = snmp_get($device, '.1.3.6.1.4.1.10002.1.1.1.1.1.0', '-OvQ');
$free  = snmp_get($device, '.1.3.6.1.4.1.10002.1.1.1.1.2.0', '-OvQ');
$mempool['total'] = $total * 1024;
$mempool['free']  = $free * 1024;
$mempool['used']  = ($mempool['total'] - $mempool['free']);
