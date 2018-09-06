<?php

$graph_type   = 'sensor_fanspeed';
$sensor_class = 'fanspeed';

if ($device["os"] == 'edgeswitch') {
    $sensor_unit  = '%';
} else {
    $sensor_unit  = 'rpm';
}

$sensor_type  = 'Fanspeed';

require 'pages/device/overview/generic/sensor.inc.php';
