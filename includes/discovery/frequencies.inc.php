<?php
echo("Frequencies: ");

include_dir("includes/discovery/frequencies");

if($debug) { print_r($valid['freq']); }

check_valid_sensors($device, 'freq', $valid_sensor);

echo("\n");
?>
