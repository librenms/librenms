<?php
echo("Current: ");

include_dir("includes/discovery/current");

if ($debug) { print_r($valid['current']); }

check_valid_sensors($device, 'current', $valid_sensor);

echo("\n");

?>