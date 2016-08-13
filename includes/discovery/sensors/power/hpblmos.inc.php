<?php
if ($device['os'] == 'hpblmos') {

    $sensor_type = 'hpblmos_psu_usage';
    $psu_exists_oid = '.1.3.6.1.4.1.232.22.2.5.1.1.1.16.';
    $psu_usage_oid = '.1.3.6.1.4.1.232.22.2.5.1.1.1.10.';
    $psu_max_usage_oid = '.1.3.6.1.4.1.232.22.2.5.1.1.1.9.';

    for ($psuid = 1; $psuid < 7; $psuid++) {
        if (snmp_get($device, $psu_exists_oid.$psuid, '-Oqve') != 2) {
            $descr = 'PSU '.$psuid.' output';
            $value = snmp_get($device, $psu_usage_oid.$psuid, '-Oqv');
            $max_value = snmp_get($device, $psu_max_usage_oid.$psuid, '-Oqv');

            if (is_numeric($value)) {
                discover_sensor($valid['sensor'], 'power', $device, $psu_usage_oid.$psuid, $psuid, $sensor_type, $descr, 1, 1, null, null, null, $max_value, $value);
            }
        }
    }
}