<?php

use LibreNMS\Util\Rewrite;

echo 'Procurve ';

$divisor = 10000;
foreach (SnmpQuery::cache()->walk('HP-ICF-TRANSCEIVER-MIB::hpicfXcvrInfoTable')->table(1) as $index => $entry) {
    if (is_numeric($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrVoltage']) && $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrVoltage'] != 0) {
        $oid = '.1.3.6.1.4.1.11.2.14.11.5.1.82.1.1.1.1.12.' . $index;
        $dbquery = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", [$index, $device['device_id']]);
        $limit_low = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrVccLoAlarm'] / $divisor;
        $warn_limit_low = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrVccLoWarn'] / $divisor;
        $limit = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrVccHiAlarm'] / $divisor;
        $warn_limit = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrVccHiWarn'] / $divisor;
        $current = $entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrVoltage'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        $descr = Rewrite::shortenIfName($entry['HP-ICF-TRANSCEIVER-MIB::hpicfXcvrPortDesc']) . ' Voltage';
        discover_sensor(null, 'voltage', $device, $oid, 'hpicfXcvrVoltage.' . $index, 'procurve', $descr, $divisor, 1, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured, group: 'transceiver');
    }
}
