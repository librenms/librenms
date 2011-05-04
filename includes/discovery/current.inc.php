<?php
echo("Current: ");

include_dir("includes/discovery/current");

if ($debug) { print_r($valid['sensor']['current']); }

check_valid_sensors($device, 'current', $valid['sensor']);

echo("\n");

?>
