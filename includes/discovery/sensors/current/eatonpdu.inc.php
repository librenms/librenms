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

$data = snmpwalk_cache_multi_oid($device, 'outletCurrent', [], 'EATON-EPDU-MIB');
$descr = snmpwalk_cache_multi_oid($device, 'outletName', [], 'EATON-EPDU-MIB');
if (is_array($data)) {
    $cur_oid = '.1.3.6.1.4.1.534.6.6.7.6.4.1.3.';
    foreach ($data as $index => $entry) {
        $i++;
        discover_sensor($valid['sensor'], 'current', $device, $cur_oid . $index, $i, 'eatonpdu', $descr[$index]['outletName'], '1000', '1', null, null, null, null, $data[$index]['outletCurrent'], 'snmp', $index);
    }
}
