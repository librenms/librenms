<?php

echo 'Procurve ';

$entphydata = dbFetchRows("SELECT `entPhysicalIndex`, `entPhysicalClass`, `entPhysicalName` FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalClass` REGEXP 'module|sensor' ORDER BY `entPhysicalIndex`", [$device['device_id']]);

if (! empty($entphydata)) {
    $tempdata = snmpwalk_cache_multi_oid($device, 'hpicfXcvrInfoTable', [], 'HP-ICF-TRANSCEIVER-MIB');

    foreach ($entphydata as $index) {
        foreach ($tempdata as $tempindex => $value) {
            if ($index['entPhysicalIndex'] == $tempindex && $value['hpicfXcvrTemp'] != 0) {
                $cur_oid = '.1.3.6.1.4.1.11.2.14.11.5.1.82.1.1.1.';
                discover_sensor(
                    $valid['sensor'],
                    'temperature',
                    $device,
                    $cur_oid . $tempindex,
                    'hpicfXcvrTemp.' . $tempindex,
                    'procurve',
                    $index['entPhysicalName'],
                    '1',
                    '1',
                    null,
                    null,
                    null,
                    null,
                    $value['hpicfXcvrTemp'],
                    'snmp',
                    $index['entPhysicalIndex']
                );
            }
        }
    }
}

$multiplier = 1;
$divisor = 1000;
$divisor_alarm = 1000;
foreach ($pre_cache['procurve_hpicfXcvrInfoTable'] as $index => $entry) {
    if (is_numeric($entry['hpicfXcvrTemp']) && $entry['hpicfXcvrTemp'] != 0) {
        $oid = '.1.3.6.1.4.1.11.2.14.11.5.1.82.1.1.1.1.11.' . $index;
        $dbquery = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", [$index, $device['device_id']]);
        $limit_low = $entry['hpicfXcvrTempLoAlarm'] / $divisor_alarm;
        $warn_limit_low = $entry['hpicfXcvrTempLoWarn'] / $divisor_alarm;
        $limit = $entry['hpicfXcvrTempHiAlarm'] / $divisor_alarm;
        $warn_limit = $entry['hpicfXcvrTempHiWarn'] / $divisor_alarm;
        $current = $entry['hpicfXcvrTemp'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        foreach ($dbquery as $dbindex => $dbresult) {
            $descr = makeshortif($dbresult['ifDescr']) . ' Port';
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'temp-trans-' . $index, 'procurve', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }
}
