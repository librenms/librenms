<?php

$multiplier = 1;
$divisor = 1;
foreach ($pre_cache['nokiaIsamSfpPort'] as $slotId => $slot) {
    $slotName = $pre_cache['nokiaIsamSlot'][$slotId]['numBasedSlot'];
    foreach ($slot as $portId => $port) {
        $portName = $slotName . $port['numBasedPort'];
        if (isset($port['sfpDiagRxPower']) && is_numeric($port['sfpDiagRxPower'])) {
            $oid = '.1.3.6.1.4.1.637.61.1.56.5.1.7.' . $slotId . '.' . $portId;
            $descr = $portName . ' Rx Power';
            $limit_low = $port['sfpDiagRSSIRxPowerAlmLow'] ?? -22;
            $warn_limit_low = $port['sfpDiagRSSIRxPowerWarnLow'] ?? -20;
            $limit = $port['sfpDiagRSSIRxPowerAlmHigh'] ?? -3;
            $warn_limit = $port['sfpDiagRSSIRxPowerWarnHigh'] ?? -5;
            $value = $port['sfpDiagRxPower'] / $divisor;
            discover_sensor(null, 'dbm', $device, $oid, $portName . '-rx', 'nokia-isam', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp');
        }
        if (isset($port['sfpDiagTxPower']) && is_numeric($port['sfpDiagTxPower'])) {
            $oid = '.1.3.6.1.4.1.637.61.1.56.5.1.6.' . $slotId . '.' . $portId;
            $descr = $portName . ' Tx Power';
            $limit_low = $port['sfpDiagRSSITxPowerAlmLow'] ?? -9;
            $warn_limit_low = $port['sfpDiagRSSITxPowerWarnLow'] ?? -8;
            $limit = $port['sfpDiagRSSITxPowerAlmHigh'] ?? -3;
            $warn_limit = $port['sfpDiagRSSITxPowerWarnHigh'] ?? -4;
            $value = $port['sfpDiagTxPower'] / $divisor;
            discover_sensor(null, 'dbm', $device, $oid, $portName . '-tx', 'nokia-isam', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp');
        }
    }
}
