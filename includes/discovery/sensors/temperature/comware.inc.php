<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'Comware ';

$entphydata = dbFetchRows("SELECT `entPhysicalIndex`, `entPhysicalClass`, `entPhysicalName` FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalClass` REGEXP 'module|sensor' ORDER BY `entPhysicalIndex`", [$device['device_id']]);

if (! empty($entphydata)) {
    $tempdata = snmpwalk_cache_multi_oid($device, 'hh3cEntityExtTemperature', [], 'HH3C-ENTITY-EXT-MIB');
    $tempdata = snmpwalk_cache_multi_oid($device, 'hh3cEntityExtTemperatureThreshold', $tempdata, 'HH3C-ENTITY-EXT-MIB');
    foreach ($entphydata as $index) {
        foreach ($tempdata as $tempindex => $value) {
            if ($index['entPhysicalIndex'] == $tempindex && $value['hh3cEntityExtTemperature'] != 65535) {
                if ($value['hh3cEntityExtTemperatureThreshold'] != 65535) {
                    $hightemp = $value['hh3cEntityExtTemperatureThreshold'];
                } else {
                    $hightemp = null;
                }
                $cur_oid = '.1.3.6.1.4.1.25506.2.6.1.1.1.1.12.';
                discover_sensor(
                    $valid['sensor'],
                    'temperature',
                    $device,
                    $cur_oid . $tempindex,
                    'temp-' . $tempindex,
                    'comware',
                    $index['entPhysicalName'],
                    '1',
                    '1',
                    null,
                    null,
                    null,
                    $hightemp,
                    $value['hh3cEntityExtTemperature'],
                    'snmp',
                    $index['entPhysicalIndex']
                );
            }
        }
    }
}

$multiplier = 1;
$divisor = 1;
$divisor_alarm = 1000;
foreach ($pre_cache['comware_oids'] as $index => $entry) {
    if (is_numeric($entry['hh3cTransceiverTemperature']) && $entry['hh3cTransceiverTemperature'] != 2147483647 && isset($entry['hh3cTransceiverDiagnostic'])) {
        $oid = '.1.3.6.1.4.1.25506.2.70.1.1.1.15.' . $index;
        $dbquery = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", [$index, $device['device_id']]);
        $limit_low = $entry['hh3cTransceiverTempLoAlarm'] / $divisor_alarm;
        $warn_limit_low = $entry['hh3cTransceiverTempLoWarn'] / $divisor_alarm;
        $limit = $entry['hh3cTransceiverTempHiAlarm'] / $divisor_alarm;
        $warn_limit = $entry['hh3cTransceiverTempHiWarn'] / $divisor_alarm;
        $current = $entry['hh3cTransceiverTemperature'];
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        foreach ($dbquery as $dbindex => $dbresult) {
            $descr = makeshortif($dbresult['ifDescr']) . ' Module';
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'temp-trans-' . $index, 'comware', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }
}
