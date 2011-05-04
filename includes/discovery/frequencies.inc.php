<?php

echo("Frequencies: ");

include_dir("includes/discovery/frequencies");

if ($debug) { print_r($valid['frequency']); }

check_valid_sensors($device, 'frequency', $valid_sensor);

echo("\n");

?>
