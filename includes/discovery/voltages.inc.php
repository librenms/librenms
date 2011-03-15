<?php

echo("Voltages: ");

include_dir("includes/discovery/voltages");

if ($debug) { print_r($valid['voltage']); }

check_valid_sensors($device, 'voltage', $valid_sensor);

echo("\n");

?>