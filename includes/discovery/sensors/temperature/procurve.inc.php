<?php

use LibreNMS\Util\Rewrite;

echo 'Procurve ';

$divisor = 1000;
foreach (SnmpQuery::cache()->walk('HP-ICF-TRANSCEIVER-MIB::hpicfXcvrInfoTable')->table(1) as $index => $entry) {
    if (is_numeric($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrTemp']) && $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrTemp'] != 0) {
        $oid = '.1.3.6.1.4.1.11.2.14.11.5.1.82.1.1.1.1.11.' . $index;
        $limit_low = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrTempLoAlarm'] / $divisor;
        $warn_limit_low = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrTempLoWarn'] / $divisor;
        $limit = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrTempHiAlarm'] / $divisor;
        $warn_limit = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrTempHiWarn'] / $divisor;
        $current = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrTemp'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        $descr = Rewrite::shortenIfName($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrPortDesc']) . ' Temperature';
        discover_sensor(null, 'temperature', $device, $oid, 'temp-trans-' . $index, 'procurve', $descr, $divisor, 1, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured, group: 'transceiver');
    }
}
