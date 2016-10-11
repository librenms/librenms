<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com> 
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'riverbed') {
    d_echo('Riverbed');

    $oid = '.1.3.6.1.4.1.17163.1.1.2.9.0';
    $descr = 'System Temperature';
    $index = $oid;
    $temp = snmp_get($device, $oid, 'Oqv');

    if (is_numeric($temp)) {
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'riverbed', $descr, '1', '1', 0, null, null, 65, $current);
    }
}
