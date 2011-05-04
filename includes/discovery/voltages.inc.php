<?php

echo("Voltages: ");

include_dir("includes/discovery/voltages");

if ($debug) { print_r($valid['sensor']['voltage']); }

check_valid_sensors($device, 'voltage', $valid['sensor']);

echo("\n");

?>
