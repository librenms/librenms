<?php

if ($sensor['sensor_oid'] === '.1.3.6.1.4.1.318.1.1.1.2.1.3.0') {
    $sensor_value = (strtotime(date('Y-m-d'))-strtotime($sensor_value))/60;
}
