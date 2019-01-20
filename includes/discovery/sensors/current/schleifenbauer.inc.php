<?php
echo 'Schleifenbauer ';
$divisor = 100;

foreach ($pre_cache['sdbMgmtCtrlDevUnitAddress'] as $sdbMgmtCtrlDevUnitAddress => $sdbDevIdIndex) {
    foreach ($pre_cache['sdbDevInActualCurrent'][$sdbDevIdIndex] as $sdbDevInIndex => $sdbDevInActualCurrent) {
        $current_oid      = ".1.3.6.1.4.1.31034.12.1.1.2.6.1.1.5.$sdbDevIdIndex.$sdbDevInIndex";
        $current          = $sdbDevInActualCurrent / $divisor;
        $serial_input     = $pre_cache['sdbDevIdSerialNumber'][$sdbDevIdIndex] ."-L". $sdbDevInIndex;
        $descr            = "$serial_input RMS Current";
        $warn_limit       = $pre_cache['sdbDevInMaxAmps'][$sdbDevIdIndex][$sdbDevInIndex] / $divisor;
        $high_limit       = $pre_cache['sdbDevCfMaximumLoad'][$sdbDevIdIndex];

        // See includes/discovery/entity-physical/schleifenbauer.inc.php for an explanation why we set this as the entPhysicalIndex.
        $entPhysicalIndex = $sdbMgmtCtrlDevUnitAddress * 1000000 + 100000 + $sdbDevInIndex * 1000 + 120;

        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $serial_input, 'schleifenbauer', $descr, $divisor, '1', null, null, $warn_limit, $high_limit, $current, 'snmp', $entPhysicalIndex);
    }
}
