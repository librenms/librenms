<?php

echo 'Procurve ';

$multiplier = 1;
$divisor = 10000;
$divisor_alarm = 10000;
foreach ($pre_cache['procurve_hpicfXcvrInfoTable'] as $index => $entry) {
    if (is_numeric($entry['hpicfXcvrVoltage']) && $entry['hpicfXcvrVoltage'] != 0) {
        $oid = '.1.3.6.1.4.1.11.2.14.11.5.1.82.1.1.1.1.12.' . $index;
        $dbquery = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", [$index, $device['device_id']]);
        $limit_low = $entry['hpicfXcvrVccLoAlarm'] / $divisor_alarm;
        $warn_limit_low = $entry['hpicfXcvrVccLoWarn'] / $divisor_alarm;
        $limit = $entry['hpicfXcvrVccHiAlarm'] / $divisor_alarm;
        $warn_limit = $entry['hpicfXcvrVccHiWarn'] / $divisor_alarm;
        $current = $entry['hpicfXcvrVoltage'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        foreach ($dbquery as $dbindex => $dbresult) {
            $descr = makeshortif($dbresult['ifDescr']) . ' Port Supply Voltage';
            discover_sensor($valid['sensor'], 'voltage', $device, $oid, 'hpicfXcvrVoltage.' . $index, 'procurve', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }
}
