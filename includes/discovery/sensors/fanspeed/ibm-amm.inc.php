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

$descr_prefix = 'Blower ';
$oids = [
    '.1.3.6.1.4.1.2.3.51.2.2.3.20.0', // BLADE-MIB:blower1speedRPM
    '.1.3.6.1.4.1.2.3.51.2.2.3.21.0', // BLADE-MIB:blower2speedRPM
    '.1.3.6.1.4.1.2.3.51.2.2.3.22.0', // BLADE-MIB:blower3speedRPM
    '.1.3.6.1.4.1.2.3.51.2.2.3.23.0', // BLADE-MIB:blower4speedRPM
];

echo 'BLADE-MIB ';
foreach ($oids as $index => $oid) {
    $value = trim(snmp_get($device, $oid, '-Oqv'), '"');

    if (is_numeric($value)) {
        $descr = $descr_prefix . ($index + 1);
        discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, 'snmp', $descr, 1, 1, null, null, null, null, $value);
    }
}
