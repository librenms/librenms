<?php

echo 'Fanspeeds : ';

// Include all discovery modules
$include_dir = 'includes/discovery/sensors/fanspeeds';
require 'includes/include-dir.inc.php';

d_echo($valid['sensor']['fanspeed']);

check_valid_sensors($device, 'fanspeed', $valid['sensor']);

echo "\n";
