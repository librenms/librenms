<?php

use LibreNMS\Enum\Sensor;

$class = 'chromatic_dispersion';
$unit = Sensor::fromName($class);
$graph_type = 'sensor_chromatic_dispersion';

require 'sensors.inc.php';
