<?php
echo("Load: ");

// Include all discovery modules

$include_dir = "includes/discovery/load";
include("includes/include-dir.inc.php");

if ($debug) { print_r($valid['sensor']['load']); }

check_valid_sensors($device, 'load', $valid['load']);

echo("\n");

?>
