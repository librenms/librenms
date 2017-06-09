<?php
/*
 * LibreNMS Quanta LB6M CPU utilization
 *
 * Copyright (c) 2017 Mark Guzman <segfault@hasno.info
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

d_echo('Quanta CPU usage:');
//SNMPv2-SMI::enterprises.4413.1.1.1.1.4.9.0
$proc_usage = snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.4.9.0', '-Ovq');
preg_match('/([0-9]+.[0-9]+)/', $proc_usage, $usage);
$proc = $usage[0];
