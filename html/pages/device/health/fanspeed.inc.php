<?php

$class = 'fanspeed';

if ($config["os"][$device["os"]]["bad_fanspeed"]) {
    $unit = '%';
} else {
    $unit = 'RPM';
}

$graph_type = 'sensor_fanspeed';

require 'sensors.inc.php';
