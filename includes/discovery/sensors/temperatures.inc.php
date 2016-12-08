<?php

echo 'Temperatures: ';

// Include all discovery modules
$include_dir = 'includes/discovery/sensors/temperatures';
require 'includes/include-dir.inc.php';

d_echo($valid['sensor']['temperature']);

check_valid_sensors($device, 'temperature', $valid['sensor']);

echo "\n";
