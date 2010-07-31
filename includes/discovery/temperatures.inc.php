<?php

echo("Temperatures: ");

include_dir("includes/discovery/temperatures");

if($debug) { print_r($valid_sensor['temperature']); }

check_valid_sensors($device, 'temperature', $valid_sensor);

echo("\n");

?>
