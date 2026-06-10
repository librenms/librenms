<?php

preg_match('/Voltage.* ([: 0-9\.]+V)/', (string) $sensor_value, $temp_value);
$sensor_value = str_replace('V', '', $temp_value[1]);
