<?php

echo 'Procurve ';

$multiplier = 1;
$divisor = 1000;
foreach ($pre_cache['procurve_hpicfXcvrInfoTable'] as $index => $entry) {
    if (is_numeric($entry['hpicfXcvrRxPower']) && $entry['hpicfXcvrRxPower'] != -99999999 && isset($entry['hpicfXcvrDiagnosticsUpdate'])) {
        $oid = '.1.3.6.1.4.1.11.2.14.11.5.1.82.1.1.1.1.15.' . $index;
        $dbquery = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", [$index, $device['device_id']]);
        $limit_low = round(uw_to_dbm($entry['hpicfXcvrRcvPwrLoAlarm'] / 10), 2);
        $warn_limit_low = round(uw_to_dbm($entry['hpicfXcvrRcvPwrLoWarn'] / 10), 2);
        $limit = round(uw_to_dbm($entry['hpicfXcvrRcvPwrHiAlarm'] / 10), 2);
        $warn_limit = round(uw_to_dbm($entry['hpicfXcvrRcvPwrHiWarn'] / 10), 2);
        $current = $entry['hpicfXcvrRxPower'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        foreach ($dbquery as $dbindex => $dbresult) {
            $descr = makeshortif($dbresult['ifDescr']) . ' Port Receive Power';
            discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'hpicfXcvrRxPower.' . $index, 'procurve', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }

    if (is_numeric($entry['hpicfXcvrTxPower']) && $entry['hpicfXcvrTxPower'] != -99999999 && isset($entry['hpicfXcvrDiagnosticsUpdate'])) {
        $oid = '.1.3.6.1.4.1.11.2.14.11.5.1.82.1.1.1.1.14.' . $index;
        $dbquery = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", [$index, $device['device_id']]);
        $limit_low = round(uw_to_dbm($entry['hpicfXcvrPwrOutLoAlarm'] / 10), 2);
        $warn_limit_low = round(uw_to_dbm($entry['hpicfXcvrPwrOutLoWarn'] / 10), 2);
        $limit = round(uw_to_dbm($entry['hpicfXcvrPwrOutHiAlarm'] / 10), 2);
        $warn_limit = round(uw_to_dbm($entry['hpicfXcvrPwrOutHiWarn'] / 10), 2);
        $current = $entry['hpicfXcvrTxPower'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        foreach ($dbquery as $dbindex => $dbresult) {
            $descr = makeshortif($dbresult['ifDescr']) . ' Port Transmit Power';
            discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'hpicfXcvrTxPower.-' . $index, 'procurve', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }
}
