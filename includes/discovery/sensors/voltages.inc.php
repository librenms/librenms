<?php

echo 'Voltages: ';

// Include all discovery modules
$include_dir = 'includes/discovery/sensors/voltages';
require 'includes/include-dir.inc.php';

d_echo($valid['sensor']['voltage']);

check_valid_sensors($device, 'voltage', $valid['sensor']);

echo "\n";
