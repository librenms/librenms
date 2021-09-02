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

$multiplier = 1;
$divisor = 100;
foreach ($pre_cache['comware_oids'] as $index => $entry) {
    if (is_numeric($entry['hh3cTransceiverCurRXPower']) && $entry['hh3cTransceiverCurRXPower'] != 2147483647 && isset($entry['hh3cTransceiverDiagnostic'])) {
        $oid = '.1.3.6.1.4.1.25506.2.70.1.1.1.12.' . $index;
        $dbquery = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", [$index, $device['device_id']]);
        $limit_low = round(uw_to_dbm($entry['hh3cTransceiverRcvPwrLoAlarm'] / 10), 2);
        $warn_limit_low = round(uw_to_dbm($entry['hh3cTransceiverRcvPwrLoWarn'] / 10), 2);
        $limit = round(uw_to_dbm($entry['hh3cTransceiverRcvPwrHiAlarm'] / 10), 2);
        $warn_limit = round(uw_to_dbm($entry['hh3cTransceiverRcvPwrHiWarn'] / 10), 2);
        $current = $entry['hh3cTransceiverCurRXPower'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        foreach ($dbquery as $dbindex => $dbresult) {
            $descr = makeshortif($dbresult['ifDescr']) . ' Receive Power';
            discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'rx-' . $index, 'comware', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }

    if (is_numeric($entry['hh3cTransceiverCurTXPower']) && $entry['hh3cTransceiverCurTXPower'] != 2147483647 && isset($entry['hh3cTransceiverDiagnostic'])) {
        $oid = '.1.3.6.1.4.1.25506.2.70.1.1.1.9.' . $index;
        $dbquery = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", [$index, $device['device_id']]);
        $limit_low = round(uw_to_dbm($entry['hh3cTransceiverPwrOutLoAlarm'] / 10), 2);
        $warn_limit_low = round(uw_to_dbm($entry['hh3cTransceiverPwrOutLoWarn'] / 10), 2);
        $limit = round(uw_to_dbm($entry['hh3cTransceiverPwrOutHiAlarm'] / 10), 2);
        $warn_limit = round(uw_to_dbm($entry['hh3cTransceiverPwrOutHiWarn'] / 10), 2);
        $current = $entry['hh3cTransceiverCurTXPower'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        foreach ($dbquery as $dbindex => $dbresult) {
            $descr = makeshortif($dbresult['ifDescr']) . ' Transmit Power';
            discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'tx-' . $index, 'comware', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }
}
