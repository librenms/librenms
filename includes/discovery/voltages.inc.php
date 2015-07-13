<?php

echo 'Voltages: ';

// Include all discovery modules
$include_dir = 'includes/discovery/voltages';
require 'includes/include-dir.inc.php';

if ($debug) {
    print_r($valid['sensor']['voltage']);
}

check_valid_sensors($device, 'voltage', $valid['sensor']);

echo "\n";
