<?php
/*
 * LibreNMS NX-OS memory information module
 *
 * Copyright (c) 2017 Dave Bell <me@geordish.org>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$free = snmp_get($device, "etsysResourceStorageAvailable.{$mempool['mempool_index']}.ram.{$mempool['entPhysicalIndex']}", '-OvQ', 'ENTERASYS-RESOURCE-UTILIZATION-MIB');
$total  = snmp_get($device, "etsysResourceStorageSize.{$mempool['mempool_index']}.ram.{$mempool['entPhysicalIndex']}", '-OvQ', 'ENTERASYS-RESOURCE-UTILIZATION-MIB');

$mempool['used'] = (($total - $free) * 1024);
$mempool['free'] = ($free * 1024);
$mempool['total'] = ($total * 1024);
