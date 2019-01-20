<?php
echo 'Schleifenbauer ';
$divisor = 10000;

foreach ($pre_cache['sdbMgmtCtrlDevUnitAddress'] as $sdbMgmtCtrlDevUnitAddress => $sdbDevIdIndex) {
    foreach ($pre_cache['sdbDevInPowerFactor'][$sdbDevIdIndex] as $sdbDevInIndex => $sdbDevInPowerFactor) {
        $power_factor_oid = ".1.3.6.1.4.1.31034.12.1.1.2.6.1.1.4.$sdbDevIdIndex.$sdbDevInIndex";
        $power_factor     = $sdbDevInPowerFactor / $divisor;
        $serial_input     = $pre_cache['sdbDevIdSerialNumber'][$sdbDevIdIndex] ."-L". $sdbDevInIndex;
        $descr            = "$serial_input Power Factor";

        // See includes/discovery/entity-physical/schleifenbauer.inc.php for an explanation why we set this as the entPhysicalIndex.
        $entPhysicalIndex = $sdbMgmtCtrlDevUnitAddress * 1000000 + 100000 + $sdbDevInIndex * 1000 + 150;

        discover_sensor($valid['sensor'], 'power_factor', $device, $power_factor_oid, $serial_input, 'schleifenbauer', $descr, $divisor, '1', '0', null, null, '1', $power_factor, 'snmp', $entPhysicalIndex);
    }
}
