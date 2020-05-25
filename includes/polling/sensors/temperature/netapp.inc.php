<?php

preg_match_all('/([0-9]+C)+/', $sensor_value, $temps);
list(,$index) = explode('.', $sensor['sensor_index']);
$sensor_value = $temps[0][$index];
