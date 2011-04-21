<?php
echo("Power: ");

include_dir("includes/discovery/power");

if ($debug) { print_r($valid['power']); }

check_valid_sensors($device, 'power', $valid_sensor);

echo("\n");

?>