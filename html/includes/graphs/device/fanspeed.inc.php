<?php

$class     = 'fanspeed';
$unit      = '';

if ($config["os"][$device["os"]]["bad_fanspeed"]) {
    $unit_long = '%';
} else {
    $unit_long = 'RPM';
}

require 'includes/graphs/device/sensor.inc.php';
