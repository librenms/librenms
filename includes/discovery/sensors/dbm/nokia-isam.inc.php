<?php

use LibreNMS\Enum\SensorType;

$multiplier = 1;
$divisor = 1;
foreach ($pre_cache['nokiaIsamSfpPort'] as $slotId => $slot) {
    $slotName = $pre_cache['nokiaIsamSlot'][$slotId];
    foreach ($slot as $portId => $port) {
        $portName = $slotName . $port['numBasedPort'];
        if (isset($port['SFP-MIB::sfpDiagRxPower']) && is_numeric($port['SFP-MIB::sfpDiagRxPower'])) {
            $oid = '.1.3.6.1.4.1.637.61.1.56.5.1.7.' . $slotId . '.' . $portId;
            $descr = $portName . ' Rx Power';
            $limit_low = $port['SFP-MIB::sfpDiagRSSIRxPowerAlmLow'] ?? -22;
            $warn_limit_low = $port['SFP-MIB::sfpDiagRSSIRxPowerWarnLow'] ?? -20;
            $limit = $port['SFP-MIB::sfpDiagRSSIRxPowerAlmHigh'] ?? -3;
            $warn_limit = $port['SFP-MIB::sfpDiagRSSIRxPowerWarnHigh'] ?? -5;
            $value = $port['SFP-MIB::sfpDiagRxPower'] / $divisor;
            discover_sensor(null, SensorType::Dbm, $device, $oid, $portName . '-rx', 'nokia-isam', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp');
        }
        if (isset($port['SFP-MIB::sfpDiagTxPower']) && is_numeric($port['SFP-MIB::sfpDiagTxPower'])) {
            $oid = '.1.3.6.1.4.1.637.61.1.56.5.1.6.' . $slotId . '.' . $portId;
            $descr = $portName . ' Tx Power';
            $limit_low = $port['SFP-MIB::sfpDiagRSSITxPowerAlmLow'] ?? -9;
            $warn_limit_low = $port['SFP-MIB::sfpDiagRSSITxPowerWarnLow'] ?? -8;
            $limit = $port['SFP-MIB::sfpDiagRSSITxPowerAlmHigh'] ?? -3;
            $warn_limit = $port['SFP-MIB::sfpDiagRSSITxPowerWarnHigh'] ?? -4;
            $value = $port['SFP-MIB::sfpDiagTxPower'] / $divisor;
            discover_sensor(null, SensorType::Dbm, $device, $oid, $portName . '-tx', 'nokia-isam', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp');
        }
    }
}
