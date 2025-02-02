<?php

use LibreNMS\Util\Rewrite;

$divisor = 1000000;
foreach (SnmpQuery::cache()->walk('HP-ICF-TRANSCEIVER-MIB::hpicfXcvrInfoTable')->table(1) as $index => $entry) {
    if (is_numeric($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrBias']) && $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrBias'] != 0) {
        $oid = '.1.3.6.1.4.1.11.2.14.11.5.1.82.1.1.1.1.13.' . $index;
        $limit_low = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrBiasLoAlarm'] / $divisor;
        $warn_limit_low = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrBiasLoWarn'] / $divisor;
        $limit = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrBiasHiAlarm'] / $divisor;
        $warn_limit = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrBiasHiWarn'] / $divisor;
        $current = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrBias'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        $descr = Rewrite::shortenIfName($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrPortDesc']) . ' Bias Current';
        discover_sensor(null, 'current', $device, $oid, 'hpicfXcvrBias.' . $index, 'procurve', $descr, $divisor, 1, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured, group: 'transceiver');
    }
}
