<?php
echo 'Schleifenbauer ';

foreach ($pre_cache['sdbMgmtCtrlDevUnitAddress'] as $sdbMgmtCtrlDevUnitAddress => $sdbDevIdIndex) {
    foreach ($pre_cache['sdbDevInKWhTotal'][$sdbDevIdIndex] as $sdbDevInIndex => $sdbDevInKWhTotal) {
        $power_consumed_oid = ".1.3.6.1.4.1.31034.12.1.1.2.6.1.1.2.$sdbDevIdIndex.$sdbDevInIndex";
        $serial_input     = $pre_cache['sdbDevIdSerialNumber'][$sdbDevIdIndex] ."-L". $sdbDevInIndex;
        $descr            = "$serial_input Lifetime kWh Total";

        // See includes/discovery/entity-physical/schleifenbauer.inc.php for an explanation why we set this as the entPhysicalIndex.
        $entPhysicalIndex = $sdbMgmtCtrlDevUnitAddress * 1000000 + 100000 + $sdbDevInIndex * 1000 + 140;

        discover_sensor($valid['sensor'], 'power_consumed', $device, $power_consumed_oid, $serial_input, 'schleifenbauer', $descr, '1', '1', '0', null, null, '16777215', $sdbDevInKWhTotal, 'snmp', $entPhysicalIndex);
    }
}
