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

$mempool['total'] = snmp_get($device, 'coUsInfoRamTotal.0', '-OvQU', 'COLUBRIS-USAGE-INFORMATION-MIB');
$mempool['free']  = snmp_get($device, 'coUsInfoRamFree.0', '-OvQU', 'COLUBRIS-USAGE-INFORMATION-MIB');
$mempool['used']  = $mempool['total'] - $mempool['free'];
