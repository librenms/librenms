<?php

$class     = 'fanspeed';
$unit      = '';

if ($device["os"] == 'edgeswitch') {
    $unit_long = ' %';
} else {
    $unit_long = ' RPM';
}


require 'includes/graphs/device/sensor.inc.php';
