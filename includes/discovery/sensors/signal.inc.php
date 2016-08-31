<?php

echo 'Signal: ';
// Include all discovery modules
$include_dir = 'includes/discovery/sensors/signal';
require 'includes/include-dir.inc.php';

d_echo($valid['sensor']['signal']);

check_valid_sensors($device, 'signal', $valid['sensor']);

echo "\n";
