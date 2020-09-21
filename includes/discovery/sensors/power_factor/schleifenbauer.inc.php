<?php

echo 'Schleifenbauer ';
$divisor = 10000;

foreach ($pre_cache['sdbMgmtCtrlDevUnitAddress'] as $sdbMgmtCtrlDevUnitAddress => $sdbDevIdIndex) {
    foreach ($pre_cache['sdbDevInPowerFactor'][$sdbDevIdIndex] as $sdbDevInIndex => $sdbDevInPowerFactor) {
        $name = trim($pre_cache['sdbDevInName'][$sdbDevIdIndex][$sdbDevInIndex], '"');
        $power_factor_oid = ".1.3.6.1.4.1.31034.12.1.1.2.6.1.1.4.$sdbDevIdIndex.$sdbDevInIndex";
        $power_factor = $sdbDevInPowerFactor / $divisor;
        $serial_input = $pre_cache['sdbDevIdSerialNumber'][$sdbDevIdIndex] . '-L' . $sdbDevInIndex;
        $descr = $name ?: "$serial_input Power Factor";

        // See includes/discovery/entity-physical/schleifenbauer.inc.php for an explanation why we set this as the entPhysicalIndex.
        $entPhysicalIndex = $sdbMgmtCtrlDevUnitAddress * 1000000 + 100000 + $sdbDevInIndex * 1000 + 150;

        discover_sensor($valid['sensor'], 'power_factor', $device, $power_factor_oid, $serial_input, 'schleifenbauer', $descr, $divisor, '1', '0', null, null, '1', $power_factor, 'snmp', $entPhysicalIndex);
    }
}

$unit = current($pre_cache['sdbMgmtCtrlDevUnitAddress']);
foreach ($pre_cache['sdbDevOutMtPowerFactor'] as $sdbDevOutMtIndex => $sdbDevOutMtPowerFactor) {
    $name = trim($pre_cache['sdbDevOutName'][$sdbDevOutMtIndex], '"');
    $power_factor_oid = ".1.3.6.1.4.1.31034.12.1.1.2.7.2.1.4.$unit.$sdbDevOutMtIndex";
    $power_factor = $sdbDevOutMtPowerFactor / $divisor;
    $serial_input = $pre_cache['sdbDevIdSerialNumber'][$unit] . ' Outlet ' . $sdbDevOutMtIndex;
    $descr = $name ?: "$serial_input Power Factor";

    // See includes/discovery/entity-physical/schleifenbauer.inc.php for an explanation why we set this as the entPhysicalIndex.
    $entPhysicalIndex = $sdbMgmtCtrlDevUnitAddress * 1000000 + 200000 + $sdbDevOutMtIndex * 1000 + 150;

    discover_sensor($valid['sensor'], 'power_factor', $device, $power_factor_oid, $serial_input, 'schleifenbauer', $descr, $divisor, '1', '0', null, null, '1', $power_factor, 'snmp', $entPhysicalIndex);
}
