<?php

echo("Fanspeeds : ");

/// Include all discovery modules

$include_dir = "includes/discovery/fanspeeds";
include("includes/include-dir.inc.php");

if ($debug) { print_r($valid['sensor']['fanspeed']); }

check_valid_sensors($device, 'fanspeed', $valid['sensor']);

echo("\n");

?>
