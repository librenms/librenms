<?php
echo("Current: ");

// Include all discovery modules

$include_dir = "includes/discovery/current";
include("includes/include-dir.inc.php");

if ($debug) { print_r($valid['sensor']['current']); }

check_valid_sensors($device, 'current', $valid['sensor']);

echo("\n");

?>
