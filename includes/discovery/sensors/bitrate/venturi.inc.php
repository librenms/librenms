<?php

d_echo('Venturi count sensors');

if (is_array($pre_cache['VENTURI-SERVER-SYSTEM-MIB::vServerGeneralScalars'])) {
    $sensor_value = $pre_cache['VENTURI-SERVER-SYSTEM-MIB::vServerGeneralScalars'][0]['VENTURI-SERVER-SYSTEM-MIB::vServerMaxTcpBandwidth'];
    d_echo('Venturi bitrate sensors');
    $sensor_oid = '.1.3.6.1.4.1.3382.12.1.2.1.1.10.0';
    $sensor_index = 10;
    $sensor_name = 'max_tcp_bandwidth';
    $sensor_descr = 'Max TCP Bandwidth';
    $sensor_divisor = 1;
    $sensor_multiplier = 1000;
    $sensor_group = 'System Capacity';

    discover_sensor(
        null,
        'bitrate',
        $device,
        $sensor_oid,
        $sensor_index,
        $sensor_name,
        $sensor_descr,
        $sensor_divisor,
        $sensor_multiplier,
        null,
        null,
        null,
        null,
        $sensor_value,
        'snmp',
        null,
        null,
        null,
        $sensor_group,
        'gauge'
    );
}