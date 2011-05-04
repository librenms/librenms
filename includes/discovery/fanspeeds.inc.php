<?php

echo("Fanspeeds : ");
include_dir("includes/discovery/fanspeeds");

if ($debug) { print_r($valid['sensor']['fanspeed']); }

check_valid_sensors($device, 'fanspeed', $valid['sensor']);

echo("\n");

?>
