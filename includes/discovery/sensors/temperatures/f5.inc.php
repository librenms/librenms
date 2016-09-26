<?php

if ($device['os'] == 'f5') {
    $apc_env_data = snmpwalk_cache_oid($device, 'sysChassisTempTable', array(), 'F5-BIGIP-SYSTEM-MIB');

    foreach (array_keys($apc_env_data) as $index) {
        $descr           = " temp.".$apc_env_data[$index]['sysChassisTempIndex'];
        $current         = $apc_env_data[$index]['sysChassisTempTemperature'];
        $sensorType      = 'f5';
        $oid             = '.1.3.6.1.4.1.3375.2.1.3.2.3.2.1.2.'.$index;
        $low_limit       =  null;
        $low_warn_limit  =  null;
        $high_warn_limit =  null;
        $high_limit      =  null;
        echo $descr;

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensorType, $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
    }
}//end if
