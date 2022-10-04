<?php

use LibreNMS\Enum\Sensor;

$graph_type = 'sensor_chromatic_dispersion';
$class = 'chromatic_dispersion';
$unit = Sensor::fromName($class);

require 'includes/html/pages/health/sensors.inc.php';
