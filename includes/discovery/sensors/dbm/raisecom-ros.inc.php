<?php

echo 'Raisecom';

$multiplier = 1;
$divisor = 1000;
foreach ($pre_cache['rosMgmtOpticalTransceiverDDMTable'] as $index => $data) {
    foreach ($data as $key => $value) {
        if (($key == 'txPower') && is_numeric($value['rosMgmtOpticalTransceiverParameterValue']) && ($value['rosMgmtOpticalTransceiverDDMValidStatus'] == 1)) {
            $oid = '.1.3.6.1.4.1.8886.1.18.2.2.1.1.2.' . $index . '.3';
            $sensor_type = 'rosMgmtOpticalTransceiverTxPower';
            $port = get_port_by_index_cache($device['device_id'], str_replace('1.', '', $index));
            $descr = $port['ifDescr'] . ' Transmit Power';
            $low_limit = $value['rosMgmtOpticalTransceiverParamLowAlarmThresh'] / $divisor;
            $low_warn_limit = $value['rosMgmtOpticalTransceiverParamLowWarningThresh'] / $divisor;
            $warn_limit = $value['rosMgmtOpticalTransceiverParamHighWarningThresh'] / $divisor;
            $high_limit = $value['rosMgmtOpticalTransceiverParamHighAlarmThresh'] / $divisor;
            $current = $value['rosMgmtOpticalTransceiverParameterValue'] / $divisor;
            $entPhysicalIndex = $index;
            $entPhysicalIndex_measured = 'ports';
            if ($port['ifAdminStatus'] == 'up') {
                discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'tx-' . $index, $sensor_type, $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
            }
        }
        if (($key == 'rxPower') && is_numeric($value['rosMgmtOpticalTransceiverParameterValue']) && ($value['rosMgmtOpticalTransceiverDDMValidStatus'] != 0)) {
            $oid = '.1.3.6.1.4.1.8886.1.18.2.2.1.1.2.' . $index . '.4';
            $sensor_type = 'rosMgmtOpticalTransceiverRxPower';
            $port = get_port_by_index_cache($device['device_id'], str_replace('1.', '', $index));
            $descr = $port['ifDescr'] . ' Receive Power';
            $low_limit = $value['rosMgmtOpticalTransceiverParamLowAlarmThresh'] / $divisor;
            $low_warn_limit = $value['rosMgmtOpticalTransceiverParamLowWarningThresh'] / $divisor;
            $warn_limit = $value['rosMgmtOpticalTransceiverParamHighWarningThresh'] / $divisor;
            $high_limit = $value['rosMgmtOpticalTransceiverParamHighAlarmThresh'] / $divisor;
            $current = $value['rosMgmtOpticalTransceiverParameterValue'] / $divisor;
            $entPhysicalIndex = $index;
            $entPhysicalIndex_measured = 'ports';
            if ($port['ifAdminStatus'] == 'up') {
                discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'rx-' . $index, $sensor_type, $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
            }
        }
    }
}
