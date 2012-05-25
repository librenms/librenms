<?php

echo("Power: ");

// Include all discovery modules

$include_dir = "includes/discovery/power";
include("includes/include-dir.inc.php");

if ($debug) { print_r($valid['sensor']['power']); }

check_valid_sensors($device, 'power', $valid['sensor']);

echo("\n");

?>
