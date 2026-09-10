<?php

preg_match('/Current.* ([: 0-9\.]+A)/', (string) $sensor_value, $temp_value);
$value = str_replace('A', '', $temp_value[1]);
