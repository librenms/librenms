<?php

echo 'Schleifenbauer ';
$divisor = 100;

foreach ($pre_cache['sdbMgmtCtrlDevUnitAddress'] as $sdbMgmtCtrlDevUnitAddress => $sdbDevIdIndex) {
    foreach ($pre_cache['sdbDevInActualCurrent'][$sdbDevIdIndex] as $sdbDevInIndex => $sdbDevInActualCurrent) {
        $name = trim($pre_cache['sdbDevInName'][$sdbDevIdIndex][$sdbDevInIndex], '"');
        $current_oid = ".1.3.6.1.4.1.31034.12.1.1.2.6.1.1.5.$sdbDevIdIndex.$sdbDevInIndex";
        $current = $sdbDevInActualCurrent / $divisor;
        $serial_input = $pre_cache['sdbDevIdSerialNumber'][$sdbDevIdIndex] . '-L' . $sdbDevInIndex;
        $descr = $name ?: "$serial_input RMS Current";
        $warn_limit = $pre_cache['sdbDevInMaxAmps'][$sdbDevIdIndex][$sdbDevInIndex] / $divisor;
        $high_limit = $pre_cache['sdbDevCfMaximumLoad'][$sdbDevIdIndex];

        // See includes/discovery/entity-physical/schleifenbauer.inc.php for an explanation why we set this as the entPhysicalIndex.
        $entPhysicalIndex = $sdbMgmtCtrlDevUnitAddress * 1000000 + 100000 + $sdbDevInIndex * 1000 + 120;

        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $serial_input, 'schleifenbauer', $descr, $divisor, '1', null, null, $warn_limit, $high_limit, $current, 'snmp', $entPhysicalIndex);
    }
}

$unit = current($pre_cache['sdbMgmtCtrlDevUnitAddress']);
foreach ($pre_cache['sdbDevOutMtActualCurrent'] as $sdbDevOutMtIndex => $sdbDevOutMtActualCurrent) {
    $name = trim($pre_cache['sdbDevOutName'][$sdbDevOutMtIndex], '"');
    $current_oid = ".1.3.6.1.4.1.31034.12.1.1.2.7.2.1.5.$unit.$sdbDevOutMtIndex";
    $current = $sdbDevOutMtActualCurrent / $divisor;
    $serial_output = $pre_cache['sdbDevIdSerialNumber'][$unit] . ' Outlet ' . $sdbDevOutMtIndex;
    $descr = $name ?: "$serial_output RMS Current";
    $warn_limit = $pre_cache['sdbDevOutMtMaxAmps'][$sdbDevOutMtIndex] / $divisor;
    $high_limit = $pre_cache['sdbDevCfMaximumLoad'][$unit];

    // See includes/discovery/entity-physical/schleifenbauer.inc.php for an explanation why we set this as the entPhysicalIndex.
    $entPhysicalIndex = $sdbMgmtCtrlDevUnitAddress * 1000000 + 200000 + $sdbDevOutMtIndex * 1000 + 120;

    discover_sensor($valid['sensor'], 'current', $device, $current_oid, $serial_output, 'schleifenbauer', $descr, $divisor, '1', null, null, $warn_limit, $high_limit, $current, 'snmp', $entPhysicalIndex);
}
