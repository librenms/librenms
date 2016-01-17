<?php

echo 'Frequencies: ';

// Include all discovery modules
$include_dir = 'includes/discovery/sensors/frequencies';
require 'includes/include-dir.inc.php';

d_echo($valid['sensor']['frequency']);

check_valid_sensors($device, 'frequency', $valid['sensor']);

echo "\n";
