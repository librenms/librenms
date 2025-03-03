<?php

use LibreNMS\Util\Rewrite;

echo 'Procurve ';

$divisor = 1000;
foreach (SnmpQuery::cache()->walk('HP-ICF-TRANSCEIVER-MIB::hpicfXcvrInfoTable')->table(1) as $index => $entry) {
    if (is_numeric($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrRxPower']) && $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrRxPower'] != -99999999 && isset($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrDiagnosticsUpdate'])) {
        $oid = '.1.3.6.1.4.1.11.2.14.11.5.1.82.1.1.1.1.15.' . $index;
        $limit_low = round(uw_to_dbm($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrRcvPwrLoAlarm'] / 10), 2);
        $warn_limit_low = round(uw_to_dbm($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrRcvPwrLoWarn'] / 10), 2);
        $limit = round(uw_to_dbm($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrRcvPwrHiAlarm'] / 10), 2);
        $warn_limit = round(uw_to_dbm($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrRcvPwrHiWarn'] / 10), 2);
        $current = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrRxPower'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        $descr = Rewrite::shortenIfName($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrPortDesc']) . ' Rx Power';
        discover_sensor(null, 'dbm', $device, $oid, 'hpicfXcvrRxPower.' . $index, 'procurve', $descr, $divisor, 1, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured, group: 'transceiver');
    }

    if (is_numeric($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrTxPower']) && $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrTxPower'] != -99999999 && isset($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrDiagnosticsUpdate'])) {
        $oid = '.1.3.6.1.4.1.11.2.14.11.5.1.82.1.1.1.1.14.' . $index;
        $limit_low = round(uw_to_dbm($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrPwrOutLoAlarm'] / 10), 2);
        $warn_limit_low = round(uw_to_dbm($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrPwrOutLoWarn'] / 10), 2);
        $limit = round(uw_to_dbm($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrPwrOutHiAlarm'] / 10), 2);
        $warn_limit = round(uw_to_dbm($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrPwrOutHiWarn'] / 10), 2);
        $current = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrTxPower'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        $descr = Rewrite::shortenIfName($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrPortDesc']) . ' Tx Power';
        discover_sensor(null, 'dbm', $device, $oid, 'hpicfXcvrTxPower.-' . $index, 'procurve', $descr, $divisor, 1, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured, group: 'transceiver');
    }
}
