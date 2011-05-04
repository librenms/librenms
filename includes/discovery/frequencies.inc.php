<?php

echo("Frequencies: ");

include_dir("includes/discovery/frequencies");

if ($debug) { print_r($valid['sensor']['frequency']); }

check_valid_sensors($device, 'frequency', $valid['sensor']);

echo("\n");

?>
