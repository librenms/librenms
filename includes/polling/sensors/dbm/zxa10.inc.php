<?php

if ($sensor['sensor_type'] == 'zxa10_onu_from_olt_rx') {
    $i = str_replace('zxAnPonRxOpticalPower.','',$sensor['sensor_index']);
    $sensor_value = $sensor_cache['zxa10_onu'][$i]['zxAnPonRxOpticalPower'];

    if ($sensor_value == -80000) //if -80000 making it -40 dBm
    $sensor_value = -40000; //no signal
}

if ($sensor['sensor_type'] == 'zxa10_olt_from_onu_rx') {
    $i = str_replace('zxAnOpticalIfRxPwrCurrValue.','',$sensor['sensor_index']);
    $sensor_value = $sensor_cache['zxa10_olt'][$i]['zxAnOpticalIfRxPwrCurrValue'];
    
    if ($sensor_value == -80000) //if -80000 making it -40 dBm
    $sensor_value = -40000; //no signal
    
}