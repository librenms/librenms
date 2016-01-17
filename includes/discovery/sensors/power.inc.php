<?php

echo 'Power: ';

// Include all discovery modules
$include_dir = 'includes/discovery/sensors/power';
require 'includes/include-dir.inc.php';

d_echo($valid['sensor']['power']);

check_valid_sensors($device, 'power', $valid['sensor']);

echo "\n";
