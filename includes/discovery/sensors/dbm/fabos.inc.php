<?php

$multiplier = 1;
$divisor = 1;

$fabosSfpRxPower = snmp_walk($device, 'swSfpRxPower', '-OsqnU', 'FA-EXT-MIB');
$fabosSfpTxPower = snmp_walk($device, 'swSfpTxPower', '-OsqnU', 'FA-EXT-MIB');

foreach (explode("\n", $fabosSfpRxPower) as $data) {
    $data = trim($data);
    if ($data) {
        list($oid, $entry) = explode(' ', $data);
        $entPhysicalIndex = substr($oid, strrpos($oid, '.'));
        $entPhysicalIndex = trim($entPhysicalIndex, '.');
        $entPhysicalIndex = $entPhysicalIndex + 1073741823;
        if (is_numeric($entry)) {
            $index = str_replace('.1.3.6.1.4.1.1588.2.1.1.1.28.1.1.4', 'swSfpRxPower', $oid);
            $limit_low = 3;
            $limit = -5;
            $entPhysicalIndex_measured = 'ports';
            $dbquery = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", [$entPhysicalIndex, $device['device_id']]);
            foreach ($dbquery as $dbindex => $dbresult) {
                $descr = makeshortif($dbresult['ifDescr']).' Receive Power';
                discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'brocade', $descr, $divisor, $multiplier, $limit_low, null, null, $limit, $entry, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
            }
        }
    }
}

foreach (explode("\n", $fabosSfpTxPower) as $data) {
    $data = trim($data);
    if ($data) {
        list($oid, $entry) = explode(' ', $data);
        $entPhysicalIndex = substr($oid, strrpos($oid, '.'));
        $entPhysicalIndex = trim($entPhysicalIndex, '.');
        $entPhysicalIndex = $entPhysicalIndex + 1073741823;
        if (is_numeric($entry)) {
            $index = str_replace('.1.3.6.1.4.1.1588.2.1.1.1.28.1.1.5', 'swSfpTxPower', $oid);
            $limit_low = 3;
            $limit = -5;
            $entPhysicalIndex_measured = 'ports';
            $dbquery = dbFetchRows("SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ? AND `ifAdminStatus` = 'up'", [$entPhysicalIndex, $device['device_id']]);
            foreach ($dbquery as $dbindex => $dbresult) {
                $descr = makeshortif($dbresult['ifDescr']).' Transmit Power';
                discover_sensor($valid['sensor'], 'dbm', $device, $oid, $index, 'brocade', $descr, $divisor, $multiplier, $limit_low, null, null, $limit, $entry, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
            }
        }
    }
}
