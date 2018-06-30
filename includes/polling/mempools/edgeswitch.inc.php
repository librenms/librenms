<?php
/*
 * LibreNMS Ubiquiti EdgeSwitch memory information module
 *
 * Copyright (c) 2016 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$total = snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.4.2.0', '-Oqv');
$free = snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.4.1.0', '-Oqv');
$mempool['total'] = $total;
$mempool['free'] = $free;
$mempool['used'] = $total - $free;
