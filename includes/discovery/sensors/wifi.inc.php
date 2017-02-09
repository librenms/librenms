<?php

echo 'WiFi: ';

// Include all discovery modules
$include_dir = 'includes/discovery/sensors/wifi/connected-clients';
require 'includes/include-dir.inc.php';

d_echo($valid['sensor']['wifi-connected-clients']);

check_valid_sensors($device, 'wifi-connected-clients', $valid['sensor']);

echo "\n";
