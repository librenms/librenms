<?php

echo 'Schleifenbauer ';
$divisor = 100;

foreach ($pre_cache['sdbMgmtCtrlDevUnitAddress'] as $sdbMgmtCtrlDevUnitAddress => $sdbDevIdIndex) {
    foreach ($pre_cache['sdbDevInActualVoltage'][$sdbDevIdIndex] as $sdbDevInIndex => $sdbDevInActualVoltage) {
        $name = trim($pre_cache['sdbDevInName'][$sdbDevIdIndex][$sdbDevInIndex], '"');
        $voltage_oid = ".1.3.6.1.4.1.31034.12.1.1.2.6.1.1.7.$sdbDevIdIndex.$sdbDevInIndex";
        $voltage = $sdbDevInActualVoltage / $divisor;
        $serial_input = $pre_cache['sdbDevIdSerialNumber'][$sdbDevIdIndex] . '-L' . $sdbDevInIndex;
        $descr = $name ?: "$serial_input Voltage";

        // See includes/discovery/entity-physical/schleifenbauer.inc.php for an explanation why we set this as the entPhysicalIndex.
        $entPhysicalIndex = $sdbMgmtCtrlDevUnitAddress * 1000000 + 100000 + $sdbDevInIndex * 1000 + 110;

        discover_sensor($valid['sensor'], 'voltage', $device, $voltage_oid, $serial_input, 'schleifenbauer', $descr, $divisor, '1', null, null, null, null, $voltage, 'snmp', $entPhysicalIndex);
    }
}

$unit = current($pre_cache['sdbMgmtCtrlDevUnitAddress']);
foreach ($pre_cache['sdbDevOutMtActualVoltage'] as $sdbDevOutMtIndex => $sdbDevOutMtActualVoltage) {
    $name = trim($pre_cache['sdbDevOutName'][$sdbDevOutMtIndex], '"');
    $voltage_oid = ".1.3.6.1.4.1.31034.12.1.1.2.7.2.1.7.$unit.$sdbDevOutMtIndex";
    $voltage = $sdbDevOutMtActualVoltage / $divisor;
    $serial_input = $pre_cache['sdbDevIdSerialNumber'][$unit] . ' Outlet ' . $sdbDevOutMtIndex;
    $descr = $name ?: "$serial_input Voltage";

    // See includes/discovery/entity-physical/schleifenbauer.inc.php for an explanation why we set this as the entPhysicalIndex.
    $entPhysicalIndex = $sdbMgmtCtrlDevUnitAddress * 1000000 + 200000 + $sdbDevOutMtIndex * 1000 + 110;

    discover_sensor($valid['sensor'], 'voltage', $device, $voltage_oid, $serial_input, 'schleifenbauer', $descr, $divisor, '1', null, null, null, null, $voltage, 'snmp', $entPhysicalIndex);
}
