<?php
echo 'Load: ';

// Include all discovery modules
$include_dir = 'includes/discovery/sensors/load';
require 'includes/include-dir.inc.php';

d_echo($valid['sensor']['load']);

check_valid_sensors($device, 'load', $valid['sensor']);

echo "\n";
