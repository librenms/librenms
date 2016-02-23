<?php

echo 'Battery Charge: ';

// Include all discovery modules
$include_dir = 'includes/discovery/sensors/charge';
require 'includes/include-dir.inc.php';

d_echo($valid['sensor']['charge']);

check_valid_sensors($device, 'charge', $valid['sensor']);

echo "\n";
