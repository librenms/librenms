<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
*/
echo 'PBN ';

$multiplier = 1;
$divisor = 10000;
foreach ($pre_cache['pbn_oids'] as $index => $entry) {
    if (is_numeric($entry['voltage']) && ($entry['voltage'] !== '-65535')) {
        $oid = '.1.3.6.1.4.1.11606.10.9.63.1.7.1.5.' . $index;
        $interface = get_port_by_index_cache($device['device_id'], $index)['ifDescr'];
        $descr = $interface . ' Voltage';
        $limit_low = 30000 / $divisor;
        $warn_limit_low = 32100 / $divisor;
        $limit = 35000 / $divisor;
        $warn_limit = 33000 / $divisor;
        $value = $entry['voltage'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor(null, 'voltage', $device, $oid, '' . $index, 'pbn', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
