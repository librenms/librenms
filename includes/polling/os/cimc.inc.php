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

$oids = ['cucsComputeBoardModel.1', 'cucsComputeBoardSerial.1'];
$data = snmp_get_multi($device, $oids, '-OQUs', 'CISCO-UNIFIED-COMPUTING-COMPUTE-MIB');
if (!empty($data[1]['cucsComputeBoardModel'])) {
    $hardware = $data[1]['cucsComputeBoardModel'];
}
if (!empty($data[1]['cucsComputeBoardSerial'])) {
    $serial = $data[1]['cucsComputeBoardSerial'];
}
preg_match('/(?<=Firmware Version).([^\s]+)/', $device['sysDescr'], $tv_matches);
if (isset($tv_matches[1])) {
    $version = $tv_matches[1];
}
