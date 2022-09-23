<?php

$multiplier = 1;
$divisor = 1000000;
$divisor_alarm = 1000000;
foreach ($pre_cache['procurve_hpicfXcvrInfoTable'] as $index => $entry) {
    if (is_numeric($entry['hpicfXcvrBias']) && $entry['hpicfXcvrBias'] != 0) {
        $oid = '.1.3.6.1.4.1.11.2.14.11.5.1.82.1.1.1.1.13.' . $index;
        $dbquery = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", [$index, $device['device_id']]);
        $limit_low = $entry['hpicfXcvrBiasLoAlarm'] / $divisor_alarm;
        $warn_limit_low = $entry['hpicfXcvrBiasLoWarn'] / $divisor_alarm;
        $limit = $entry['hpicfXcvrBiasHiAlarm'] / $divisor_alarm;
        $warn_limit = $entry['hpicfXcvrBiasHiWarn'] / $divisor_alarm;
        $current = $entry['hpicfXcvrBias'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        foreach ($dbquery as $dbindex => $dbresult) {
            $descr = makeshortif($dbresult['ifDescr']) . ' Port Bias Current';
            discover_sensor($valid['sensor'], 'current', $device, $oid, 'hpicfXcvrBias.' . $index, 'procurve', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }
}
