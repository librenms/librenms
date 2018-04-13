<?php

$oid_sensor = $sensor['sensor_oid'];
$sensor_value = trim(str_replace('"', '', $snmp_data[$oid_sensor]));
