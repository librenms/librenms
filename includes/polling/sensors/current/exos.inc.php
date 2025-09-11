<?php

preg_match('/Current.* ([: 0-9\.]+A)/', $sensor_value, $temp_value);
$value = str_replace('A', '', $temp_value[1]);
