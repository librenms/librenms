<?php

echo("Frequencies: ");

// Include all discovery modules

$include_dir = "includes/discovery/frequencies";
include("includes/include-dir.inc.php");

if ($debug) { print_r($valid['sensor']['frequency']); }

check_valid_sensors($device, 'frequency', $valid['sensor']);

echo("\n");

?>
