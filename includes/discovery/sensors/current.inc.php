<?php
echo 'Current: ';

// Include all discovery modules
$include_dir = 'includes/discovery/sensors/current';
require 'includes/include-dir.inc.php';

d_echo($valid['sensor']['current']);

check_valid_sensors($device, 'current', $valid['sensor']);

echo "\n";
