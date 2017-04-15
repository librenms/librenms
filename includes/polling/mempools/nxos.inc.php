<?php
/*
 * LibreNMS NX-OS memory information module
 *
 * Copyright (c) 2016 Dave Bell <me@geordish.org>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo "Cisco Nexus";
$used = snmp_get($device, '.1.3.6.1.4.1.9.9.109.1.1.1.1.12.1', '-OvQ');
$free = snmp_get($device, '.1.3.6.1.4.1.9.9.109.1.1.1.1.13.1', '-OvQ');

$mempool['used'] = ($used * 1024);
$mempool['free'] = ($free * 1024);
$mempool['total'] = (($used + $free) * 1024);
