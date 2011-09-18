<?php

echo("Temperatures: ");

### Include all discovery modules

$include_dir = "includes/discovery/temperatures";
include("includes/include-dir.inc.php");

if ($debug) { print_r($valid['sensor']['temperature']); }

check_valid_sensors($device, 'temperature', $valid['sensor']);

echo("\n");

?>
