<?php
echo 'Runtime: ';
// Include all discovery modules
$include_dir = 'includes/discovery/sensors/runtime';
require 'includes/include-dir.inc.php';
d_echo($valid['sensor']['runtime']);
check_valid_sensors($device, 'runtime', $valid['sensor']);
echo "\n";
