<?php

$sensor_value = trim(str_replace('"', '', snmp_get($device, $sensor['sensor_oid'], '-OUqnv', '')));
preg_match_all('/([0-9]+C)+/', $sensor_value, $temps);
list(,$index) = explode('.', $sensor['sensor_index']);
$sensor_value = $temps[0][$index];
