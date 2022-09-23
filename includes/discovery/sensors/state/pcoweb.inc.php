<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$divisor = '1';
$type = 'pcoweb';
$compressors = [
    '.1.3.6.1.4.1.9839.2.1.1.1.0', //compressore1.0
    '.1.3.6.1.4.1.9839.2.1.1.2.0', //compressore2.0
    '.1.3.6.1.4.1.9839.2.1.1.3.0', //compressore3.0
    '.1.3.6.1.4.1.9839.2.1.1.4.0',  //compressore4.0
];

foreach ($compressors as $compressor_oid) {
    $current = snmp_get($device, $compressor_oid, '-OqvU', 'CAREL-ug40cdz-MIB');
    $split_oid = explode('.', $compressor_oid);
    $number = $split_oid[count($split_oid) - 2];
    $index = 'comp_' . $number;
    $descr = 'Compressor ' . $number;
    discover_sensor($valid['sensor'], 'state', $device, $compressor_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
