<?php

echo 'Raisecom';

$multiplier = 1;
$divisor = 1000;
foreach ($pre_cache['rosMgmtOpticalTransceiverDDMTable'] as $index => $data) {
    foreach ($data as $key => $value) {
        if (($key == 'transceiverTemperature') && is_numeric($value['rosMgmtOpticalTransceiverParameterValue']) && ($value['rosMgmtOpticalTransceiverDDMValidStatus'] == 1)) {
            $oid = '.1.3.6.1.4.1.8886.60.18.1.2.2.1.1.2.' . $index . '.1';
            $sensor_type = 'rosMgmtOpticalTransceiverTemperature';
            $port_descr = get_port_by_index_cache($device['device_id'], str_replace('1.', '', $index));
            $descr = $port_descr['ifDescr'] . ' Transceiver Temperature';
            $low_limit = $value['rosMgmtOpticalTransceiverParamLowAlarmThresh'] / $divisor;
            $low_warn_limit = $value['rosMgmtOpticalTransceiverParamLowWarningThresh'] / $divisor;
            $warn_limit = $value['rosMgmtOpticalTransceiverParamHighWarningThresh'] / $divisor;
            $high_limit = $value['rosMgmtOpticalTransceiverParamHighAlarmThresh'] / $divisor;
            $current = $value['rosMgmtOpticalTransceiverParameterValue'] / $divisor;
            $entPhysicalIndex = $index;
            $entPhysicalIndex_measured = 'ports';
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'tx-' . $index, $sensor_type, $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
        }
    }
}
