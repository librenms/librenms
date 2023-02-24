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

echo 'CiscoSB';

$multiplier = 1;
$divisor = 1000000;
foreach ($pre_cache['ciscosb_rlPhyTestGetResult'] as $index => $ciscosb_data) {
    foreach ($ciscosb_data as $key => $value) {
        if (! isset($value['rlPhyTestTableTransceiverSupply'])) {
            continue;
        }

        $oid = '.1.3.6.1.4.1.9.6.1.101.90.1.2.1.3.' . $index . '.6';
        $sensor_type = 'rlPhyTestTableTransceiverSupply';
        $port_descr = get_port_by_index_cache($device['device_id'], preg_replace('/^\d+\./', '', $index));
        $descr = trim(($port_descr['ifDescr'] ?? null) . ' Supply Voltage');
        $voltage = $value['rlPhyTestTableTransceiverSupply'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        if (is_numeric($voltage) && ($value['rlPhyTestTableTransceiverTemp'] != 0)) {
            discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $sensor_type, $descr, $divisor, $multiplier, null, null, null, null, $voltage, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }
}
