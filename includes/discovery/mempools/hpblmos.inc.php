<?php
/*
 * LibreNMS HP Blade OA Memory information module
 *
 * Copyright (c) 2016 Cercel Valentin (crc@nuamchefazi.ro)
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == "hpblmos") {
    d_echo('HP OA Memory info:');
    //UCD-SNMP-MIB::memAvailReal.0
    $free = intval(preg_replace('/[^0-9]+/', '', snmp_get($device, '.1.3.6.1.4.1.2021.4.6.0', '-Oqv')), 10);
    //UCD-SNMP-MIB::memTotalReal.0
    $total = intval(preg_replace('/[^0-9]+/', '', snmp_get($device, '.1.3.6.1.4.1.2021.4.5.0', '-Oqv')), 10);
    $used = $total - $free;

    if ((is_numeric($total)) && (is_numeric($free))) {
        discover_mempool($valid_mempool, $device, 0, 'hpblmos', 'Memory', '1', null, null);
    }
}
