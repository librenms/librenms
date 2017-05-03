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

if ($device['os'] == "juniper-mss") {
    d_echo('Juniper MSS : ');
    $used = snmp_get($device, "1.3.6.1.4.1.14525.4.8.1.1.12.1.0", "-Ovq");
    $total = snmp_get($device, "1.3.6.1.4.1.14525.4.8.1.1.6.0", "-Ovq");
    $free = ($total - $used);
    $percent = (($used / $total) * 100);
    $descr = 'Memory';
    if (is_numeric($used)) {
        discover_mempool($valid_mempool, $device, '0', "juniper-mss", $descr, "1", null, null);
    }
}

unset(
    $used,
    $total,
    $free,
    $percent,
    $descr
);
