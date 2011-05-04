<?php

echo("Power: ");

include_dir("includes/discovery/power");

if ($debug) { print_r($valid['sensor']['power']); }

check_valid_sensors($device, 'power', $valid['sensor']);

echo("\n");

?>
