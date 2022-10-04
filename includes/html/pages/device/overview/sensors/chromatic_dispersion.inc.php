<?php

use LibreNMS\Enum\Sensor;

$graph_type = 'sensor_chromatic_dispersion';
$sensor_class = 'chromatic_dispersion';
$sensor_unit = Sensor::fromName($sensor_class);
$sensor_type = 'chromatic_dispersion';

require 'includes/html/pages/device/overview/generic/sensor.inc.php';
