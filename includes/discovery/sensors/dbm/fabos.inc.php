<?php

$multiplier = 1;
$divisor    = 1;

foreach ($pre_cache['fabos_Sfp'] as $index => $entry) {
    $entPhysicalIndex = substr($index,-2,2);
    $entPhysicalIndex = trim($entPhysicalIndex,'.');
    $entPhysicalIndex          = $entPhysicalIndex + 1073741823;

    if (is_numeric($entry['swSfpRxPower']) && $entry['swSfpRxPower'] != -99999999) {
        $oid                       = '.1.3.6.1.4.1.1588.2.1.1.1.28.1.1.4.' . $index;
        $current                   = $entry['swSfpRxPower'];
        $limit_low                 = 3;
        $limit                     = -5;
        $entPhysicalIndex_measured = 'ports';
        $dbquery                   = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", [$entPhysicalIndex, $device['device_id']]);
        foreach ($dbquery as $dbindex => $dbresult) {
            $descr = makeshortif($dbresult['ifDescr']) . ' Receive Power';
            discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'swSfpRxPower.' . $index, 'brocade', $descr, $divisor, $multiplier, $limit_low, null, null, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }

    if (is_numeric($entry['swSfpTxPower']) && $entry['swSfpTxPower'] != -99999999) {
        $oid                       = '.1.3.6.1.4.1.1588.2.1.1.1.28.1.1.5.' . $index;
        $current                   = $entry['swSfpTxPower'];
        $entPhysicalIndex_measured = 'ports';
        $dbquery                   = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", [$entPhysicalIndex, $device['device_id']]);
        foreach ($dbquery as $dbindex => $dbresult) {
            $descr = makeshortif($dbresult['ifDescr']) . ' Transmit Power';
            discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'swSfpTxPower.' . $index, 'brocade', $descr, $divisor, $multiplier, null, null, null, null, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }
}
