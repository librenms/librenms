<?php

preg_match_all('/([0-9]+C)+/', (string) $sensor_value, $temps);
[,$index] = explode('.', (string) $sensor['sensor_index']);
$sensor_value = $temps[0][$index];
