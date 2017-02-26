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

if ($device['group'] == 'zyxel') {
    echo 'Zyxel : ';

    $oid = '.1.3.6.1.4.1.890.1.5.8.55.12.7.0';
    $descr = 'Processor';
    $usage = snmp_get($device, $oid, '-Ovqn');

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, $oid, '0', 'zyxel', $descr, '1', $usage);
    } else {
        $oid = '.1.3.6.1.4.1.890.1.15.3.2.7.0';
        $descr = 'Processor';
        $usage = snmp_get($device, $oid, '-Ovqn');

        if (is_numeric($usage)) {
            discover_processor($valid['processor'], $device, $oid, '0', 'zyxel', $descr, '1', $usage);
        }
    }
}

unset(
    $oid,
    $descr,
    $usage
);
