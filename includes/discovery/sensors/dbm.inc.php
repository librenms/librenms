<?php

echo 'dBm: ';

// Include all discovery modules
$include_dir = 'includes/discovery/sensors/dbm';
require 'includes/include-dir.inc.php';

d_echo($valid['sensor']['dbm']);

check_valid_sensors($device, 'dbm', $valid['sensor']);

echo "\n";
