<?php

echo("Temperatures: ");

include_dir("includes/discovery/temperatures");

if ($debug) { print_r($valid['sensor']['temperature']); }

check_valid_sensors($device, 'temperature', $valid['sensor']);

echo("\n");

?>
