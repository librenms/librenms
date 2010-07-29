<?php

echo("Fanspeeds : ");
include_dir("includes/discovery/fanspeeds");

if($debug) { print_r($valid['fanspeed']); }

check_valid_sensors($device, 'fanspeed', $valid_sensor);

echo("\n");

?>
