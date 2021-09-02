<?php

$multiplier = 1;
$divisor = 1;
foreach ($pre_cache['nokiaIsamSfpPort'] as $slotId => $slot) {
    $slotName = $pre_cache['nokiaIsamSlot'][$slotId]['numBasedSlot'];
    foreach ($slot as $portId => $port) {
        $portName = $slotName . $port['numBasedPort'];
        if (is_numeric($port['sfpDiagRxPower'])) {
            $oid = '.1.3.6.1.4.1.637.61.1.56.5.1.7.' . $slotId . '.' . $portId;
            $descr = $portName . ' Rx Power';
            $limit_low = ($port['sfpDiagRSSIRxPowerAlmLow'] / $divisor) ?: -22;
            $warn_limit_low = ($port['sfpDiagRSSIRxPowerWarnLow'] / $divisor) ?: -20;
            $limit = ($port['sfpDiagRSSIRxPowerAlmHigh'] / $divisor) ?: -3;
            $warn_limit = ($port['sfpDiagRSSIRxPowerWarnHigh'] / $divisor) ?: -5;
            $value = $port['sfpDiagRxPower'] / $divisor;
            discover_sensor($valid['sensor'], 'dbm', $device, $oid, $portName . '-rx', 'nokia-isam', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
        if (is_numeric($port['sfpDiagTxPower'])) {
            $oid = '.1.3.6.1.4.1.637.61.1.56.5.1.6.' . $slotId . '.' . $portId;
            $descr = $portName . ' Tx Power';
            $limit_low = ($port['sfpDiagRSSITxPowerAlmLow'] / $divisor) ?: -9;
            $warn_limit_low = ($port['sfpDiagRSSITxPowerWarnLow'] / $divisor) ?: -8;
            $limit = ($port['sfpDiagRSSITxPowerAlmHigh'] / $divisor) ?: -3;
            $warn_limit = ($port['sfpDiagRSSITxPowerWarnHigh'] / $divisor) ?: -4;
            $value = $port['sfpDiagTxPower'] / $divisor;
            discover_sensor($valid['sensor'], 'dbm', $device, $oid, $portName . '-tx', 'nokia-isam', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }
}
