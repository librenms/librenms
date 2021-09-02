<?php

if ($device['os'] == 'junos') {
    echo 'JunOS: ';

    $sensor_exp_value = snmp_get($device, 'JNX-OPT-IF-EXT-MIB::jnxoptIfOTNPMCurrentFECBERExponent.' . $sensor['sensor_index'] . '.1', '-Oqv');
    $sensor_value = ($sensor_value) * pow(10, -$sensor_exp_value);
    unset($sensor_exp_value);
}
