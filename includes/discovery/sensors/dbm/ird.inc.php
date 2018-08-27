<?php
/*
 * LibreNMS CN sensor for the IRD PBI Headends
 * © 2018 Jozef Rebjak <jozefrebjak@icloud.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
echo 'ird_cn';
$cn_oid = '.1.3.6.1.4.1.1070.3.1.1.104.1.1.8.0';
$value = snmp_get($device, $cn_oid, '-Oqv');
$descr = 'C/N';
$divisor = 10;
if (is_numeric($value) && $value > 0){
    discover_sensor($valid['sensor'], 'dbm', $device, $cn_oid, 0, $device['os'], $descr, $divisor, 1, null, null, null, null, $value / $divisor );
 }
