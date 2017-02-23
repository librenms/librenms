<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Dave Bell <me@geordish.org>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
if ($device['os'] == 'enterasys') {
    $descr = 'Processor';
    $divisor = 10;
    $oids = snmp_walk($device, 'etsysResourceCpuLoad5min', '-Osqn', 'ENTERASYS-RESOURCE-UTILIZATION-MIB');

    foreach (explode("\n", $oids) as $data) {
        list($oid, $usage) = explode(" ", $data);
        $usage = $usage/10;
        if (is_numeric($usage)) {
            discover_processor($valid['processor'], $device, $oid, '0', 'enterasys', $descr, $divisor, $usage);
        }
    }
}
