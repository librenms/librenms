<?php

echo 'Raisecom';

$multiplier = 1;
$divisor = 1000;
foreach ($pre_cache['raisecomOpticalTransceiverDDMTable'] as $index => $data) {
    foreach ($data as $key => $value) {
        if (isset($value['raisecomOpticalTransceiverParameterValue'], $value['raisecomOpticalTransceiverDDMValidStatus']) && ($key == 'transceiverTemperature') && is_numeric($value['raisecomOpticalTransceiverParameterValue']) && ($value['raisecomOpticalTransceiverDDMValidStatus'] == 1)) {
            $oid = '.1.3.6.1.4.1.8886.1.18.2.2.1.1.2.' . $index . '.1';
            $sensor_type = 'raisecomOpticalTransceiverTemperature';
            $port = PortCache::getByIfIndex(str_replace('1.', '', $index), $device['device_id']);
            $descr = $port?->ifDescr . ' Transceiver Temperature';
            $low_limit = $value['raisecomOpticalTransceiverParamLowAlarmThresh'] / $divisor;
            $low_warn_limit = $value['raisecomOpticalTransceiverParamLowWarningThresh'] / $divisor;
            $warn_limit = $value['raisecomOpticalTransceiverParamHighWarningThresh'] / $divisor;
            $high_limit = $value['raisecomOpticalTransceiverParamHighAlarmThresh'] / $divisor;
            $current = $value['raisecomOpticalTransceiverParameterValue'] / $divisor;
            $entPhysicalIndex = $index;
            $entPhysicalIndex_measured = 'ports';
            discover_sensor(null, 'temperature', $device, $oid, 'tx-' . $index, $sensor_type, $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }
}
$descr = 'System Temperature';
$oid = '.1.3.6.1.4.1.8886.1.1.4.2.1.0'; // raisecomTemperatureValue
$value = snmp_get($device, $oid, ['-OUvq', '-Pu'], 'RAISECOM-SYSTEM-MIB', 'raisecom');

if (is_numeric($value)) {
    $low_limit = snmp_get($device, 'raisecomTemperatureThresholdLow.0', ['-OUvq', '-Pu'], 'RAISECOM-SYSTEM-MIB', 'raisecom');
    $high_limit = snmp_get($device, 'raisecomTemperatureThresholdHigh.0', ['-OUvq', '-Pu'], 'RAISECOM-SYSTEM-MIB', 'raisecom');

    discover_sensor(null, 'temperature', $device, $oid, 0, 'raisecomTemperatureValue', $descr, '1', '1', $low_limit, null, null, $high_limit, $value);
}
