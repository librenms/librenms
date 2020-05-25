<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

d_echo('Juniper MSS : ');

$memory_pool = snmp_get_multi_oid($device, ['trpzSysCpuMemoryInstantUsage.0', 'trpzSysCpuMemorySize.0'], '-OQUs', 'TRAPEZE-NETWORKS-SYSTEM-MIB');

$mempool['used'] = $memory_pool['trpzSysCpuMemoryInstantUsage.0'];
$mempool['total'] = $memory_pool['trpzSysCpuMemorySize.0'];
$mempool['free'] = ($mempool['total'] - $mempool['used']);
