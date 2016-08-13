<?php
if ($device['os'] == 'hpblmos') {

    $sensor_type = 'hpblmos_temps';
    $sensor_oid = '.1.3.6.1.4.1.232.22.2.3.1.2.1.5.';
    $sensor_value_oid = '.1.3.6.1.4.1.232.22.2.3.1.2.1.6.';

    for ($tempid = 1; $tempid < 61; $tempid++) {
        $sensor_descr = snmp_get($device, $sensor_oid.$tempid, '-Oqve');
        if ($sensor_descr != "") {
            $descr = 'Temperature '.$sensor_descr.'';
            $value = snmp_get($device, $sensor_value_oid.$tempid, '-Oqve');

            if ($value > 0) {
                discover_sensor($valid['sensor'], 'temperature', $device, $sensor_value_oid.$tempid, '1', $sensor_type, $descr, 1, 1, null, null, null, null, $value);
            }
        }
    }
}