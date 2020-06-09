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

echo 'CiscoSB: ';
$temp = snmpwalk_cache_multi_oid($device, 'rlPethPsePortEntry', [], 'CISCOSB-POE-MIB');
if (is_array($temp)) {
    $cur_oid = '.1.3.6.1.4.1.9.6.1.101.108.1.1.5.';
    $divisor = '1000';
    foreach ($temp as $index => $entry) {
        if (is_numeric($temp[$index]['rlPethPsePortOutputPower']) && $temp[$index]['rlPethPsePortOutputPower'] > 0) {
            $port_descr = get_port_by_index_cache($device['device_id'], str_replace('1.', '', $index));
            $descr = $port_descr['ifDescr'] . ' PoE';
            $highlimit = $temp[$index]['rlPethPsePortOperPowerLimit'] / $divisor;
            discover_sensor($valid['sensor'], 'power', $device, $cur_oid . $index, $index, 'ciscosb', $descr, $divisor, '1', null, null, null, $highlimit, $temp[$index]['rlPethPsePortOutputPower'] / $divisor, 'snmp', $index);
        }
    }
}
