<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'Smartoptics ';

$multiplier = 1;
$divisor = 10;
foreach ($pre_cache['smartos_oids'] as $index => $entry) {
    if (is_numeric($entry['dcpInterfaceRxPower']) && ($entry['dcpInterfaceRxPower'] !== '-990')) {
        $oid = '.1.3.6.1.4.1.30826.2.2.1.1.1.1.3.' . $index;
        $descr = $entry['dcpInterfaceName'] . ' Rx Power';
        $limit_low = -30 / $divisor;
        $warn_limit_low = -25 / $divisor;
        $limit = -2 / $divisor;
        $warn_limit = -3 / $divisor;
        $value = $entry['dcpInterfaceRxPower'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'rx-' . $index, 'smartos-dcp-m', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }

    if (is_numeric($entry['dcpInterfaceTxPower']) && ($entry['dcpInterfaceTxPower'] !== '-990')) {
        $oid = '.1.3.6.1.4.1.30826.2.2.1.1.1.1.4.' . $index;
        $descr = $entry['dcpInterfaceName'] . ' Tx Power';
        $limit_low = -30 / $divisor;
        $warn_limit_low = -25 / $divisor;
        $limit = -2 / $divisor;
        $warn_limit = -3 / $divisor;
        $value = $entry['dcpInterfaceTxPower'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'tx-' . $index, 'smartos-dcp-m', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
