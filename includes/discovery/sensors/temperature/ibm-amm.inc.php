<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Neil Lathwood <neil@lathwood.co.uk>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$oid = '.1.3.6.1.4.1.2.3.51.2.2.1.1.2.0'; // BLADE-MIB::mmTemp.0
$mmtemp = snmp_get($device, $oid, '-Oqv');

preg_match('/[\d\.]+/', $mmtemp, $temp_response);
if (! empty($temp_response[0])) {
    $mmtemp = $temp_response[0];
}

d_echo($mmtemp);

if (! empty($mmtemp)) {
    $descr = 'Management module temperature';
    $divisor = 1;
    $current = $mmtemp;
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $oid, 'ibm-amm', $descr, $divisor, '1', null, null, null, null, $current);
}

$oid = '.1.3.6.1.4.1.2.3.51.2.2.1.5.1.0'; // BLADE-MIB::frontPanelTemp.0
$fptemp = snmp_get($device, $oid, '-Oqv');

preg_match('/[\d\.]+/', $fptemp, $temp_response);
if (! empty($temp_response[0])) {
    $fptemp = $temp_response[0];
}

d_echo($fptemp);

if (! empty($fptemp)) {
    $descr = 'Front panel temperature';
    $divisor = 1;
    $current = $fptemp;
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $oid, 'ibm-amm', $descr, $divisor, '1', null, null, null, null, $current);
}
