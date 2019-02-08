<?php
echo 'NOKIA-ISAM ';

$multiplier = 1;
$divisor    = 1;
foreach ($pre_cache['nokiaIsamSlotTemperature'] as $slotId => $slot) {
    $slotName = $pre_cache['nokiaIsamSlot'][$slotId]['numBasedSlot'];
    foreach ($slot as $sensorId => $sensor) {
        if (is_numeric($sensor['eqptBoardThermalSensorActualTemperature'])) {
            $oid = '.1.3.6.1.4.1.637.61.1.23.10.1.2.'.$slotId.'.'.$sensorId;
            $descr = $slotName.' Sensor '.$sensorId;
            $limit = $sensor['eqptBoardThermalSensorShutdownThresholdHigh']/$divisor;
            $warn_limit = $sensor['eqptBoardThermalSensorTcaThresholdHigh']/$divisor;
            $value = $sensor['eqptBoardThermalSensorActualTemperature']/$divisor;
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $slotName.'.'.$sensorId.'-temp', 'nokia-isam', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }
}
