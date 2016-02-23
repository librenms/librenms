<?php

echo 'Humidity : ';

// Include all discovery modules
$include_dir = 'includes/discovery/sensors/humidity';
require 'includes/include-dir.inc.php';

d_echo($valid['sensor']['humidity']);

check_valid_sensors($device, 'humidity', $valid['sensor']);

echo "\n";
