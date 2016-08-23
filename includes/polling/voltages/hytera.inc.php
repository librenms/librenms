<?php


$sensor_value = hytera_h2f(str_replace("\"", "",snmp_get($device, $sensor['sensor_oid'], "-OUqnv", "")),2);
