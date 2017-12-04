<?php
/*
 * LibreNMS 
 *
 * Copyright (c) 2017 Martin Zatloukal <slezi2@pvfree.net>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (!empty(snmp_get($device, '.1.3.6.1.4.1.259.10.1.24.1.39.3.1.0', '-Ovqn'))) { //ECS4510
         $total = snmp_get($device, '.1.3.6.1.4.1.259.10.1.24.1.39.3.1.0', '-Ovqn');
         $avail = snmp_get($device, '.1.3.6.1.4.1.259.10.1.24.1.39.3.3.0', '-Oqvn');
} elseif (!empty(snmp_get($device, '.1.3.6.1.4.1.259.10.1.22.1.39.3.1.0', '-Ovqn'))) { //ECS3528
         $total = snmp_get($device, '.1.3.6.1.4.1.259.10.1.22.1.39.3.1.0', '-Ovqn');
         $avail = snmp_get($device, '.1.3.6.1.4.1.259.10.1.22.1.39.3.3.0', '-Oqvn');
} elseif (!empty(snmp_get($device, '.1.3.6.1.4.1.259.10.1.45.1.39.3.1.0', '-Ovqn'))) { //ECS4120
         $total = snmp_get($device, '.1.3.6.1.4.1.259.10.1.45.1.39.3.1.0', '-Ovqn');
         $avail = snmp_get($device, '.1.3.6.1.4.1.259.10.1.45.1.39.3.3.0', '-Oqvn');
} elseif (!empty(snmp_get($device, '.1.3.6.1.4.1.259.10.1.42.101.1.39.3.1.0', '-Ovqn'))) { //ECS4210
         $total = snmp_get($device, '.1.3.6.1.4.1.259.10.1.42.101.1.39.3.1.0', '-Ovqn');
         $avail = snmp_get($device, '.1.3.6.1.4.1.259.10.1.42.101.1.39.3.3.0', '-Oqvn');
} else {
         $total = snmp_get($device, '.1.3.6.1.4.1.259.10.1.27.1.39.3.1.0', '-Oqvn'); //ECS3510
         $avail = snmp_get($device, '.1.3.6.1.4.1.259.10.1.27.1.39.3.3.0', '-Oqvn');
};

$mempool['total'] = $total;
$mempool['free'] = $avail;
$mempool['used'] = $total - $avail;
