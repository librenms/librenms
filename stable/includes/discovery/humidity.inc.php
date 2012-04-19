<?php

echo("Humidity : ");

### Include all discovery modules

$include_dir = "includes/discovery/humidity";
include("includes/include-dir.inc.php");

if ($debug) { print_r($valid['sensor']['humidity']); }

check_valid_sensors($device, 'humidity', $valid['sensor']);

echo("\n");

?>
