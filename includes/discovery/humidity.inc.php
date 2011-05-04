<?php

echo("Humidity : ");

include_dir("includes/discovery/humidity");

if ($debug) { print_r($valid['sensor']['humidity']); }

check_valid_sensors($device, 'humidity', $valid['sensor']);

echo("\n");

?>
