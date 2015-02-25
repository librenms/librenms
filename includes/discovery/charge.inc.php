<?php

echo("Bettery Charge: ");

// Include all discovery modules

$include_dir = "includes/discovery/charge";
include("includes/include-dir.inc.php");

if ($debug) { print_r($valid['sensor']['charge']); }

check_valid_sensors($device, 'charge', $valid['sensor']);

echo("\n");

?>
