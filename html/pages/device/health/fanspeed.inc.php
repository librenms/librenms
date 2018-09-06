<?php

$class = 'fanspeed';

if ($device["os"] == 'edgeswitch') {
    $unit = '%';
} else {
    $unit = 'RPM';
}

$graph_type = 'sensor_fanspeed';

require 'sensors.inc.php';
