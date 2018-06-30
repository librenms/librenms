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

if (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.24.')) { //ECS4510
    $temp_mibs = 'ECS4510-MIB';
};

if (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.22.')) { //ECS3528
    $temp_mibs = 'ES3528MV2-MIB';
};

if (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.45.')) { //ECS4120
    $temp_mibs = 'ECS4120-MIB';
};

if (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.42.')) { //ECS4210
    $temp_mibs = 'ECS4210-MIB';
};

if (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.27.')) { //ECS3510
    $temp_mibs = 'ECS3510-MIB';
};

$temp_data = snmp_get_multi_oid($device, 'memoryTotal.0 memoryFreed.0', '-OUQs', $temp_mibs);
$total = $temp_data['memoryTotal.0'];
$avail = $temp_data['memoryFreed.0'];

$mempool['total'] = $total;
$mempool['free'] = $avail;
$mempool['used'] = $total - $avail;

unset($temp_mibs, $temp_data);
