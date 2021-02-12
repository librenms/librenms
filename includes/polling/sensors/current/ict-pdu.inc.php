<?php

$oid_sensor = $sensor['sensor_oid'];

if ($oid_sensor == '.1.3.6.1.4.1.39145.10.8.1.4.0') {
    $sensor_value = abs(trim(str_replace('"', '', $snmp_data[$oid_sensor])));
} elseif (preg_match('/.1.3.6.1.4.1.39145.10.8.1.3|.1.3.6.1.4.1.39145.10.7.0/i', $oid_sensor)) {
    $sensor_value = abs(trim(str_replace('"', '', $snmp_data[$sensor['sensor_oid']])));
} else {
    $sensor_value = trim(str_replace('"', '', $snmp_data[$sensor['sensor_oid'] . '.0']));
}
