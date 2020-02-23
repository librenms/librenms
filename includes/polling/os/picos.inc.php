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

preg_match("/ for ([^\s]*)/m", $device['sysDescr'], $matches);

if (!empty($matches[1])) {
    $hardware .= trim($matches[1]);
}

$oidList = [
    'PICA-PRIVATE-MIB::hostStatusGroup.16.0',
];

foreach ($oidList as $oid) {
    $serial_tmp = snmp_get($device, $oid, '-OQv');
    if (!empty($serial_tmp)) {
        $serial = $serial_tmp;
        break;
    }
}
