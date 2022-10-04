<?php

use LibreNMS\Enum\Sensor;

$class = 'chromatic_dispersion';
$unit = Sensor::fromName($class);
$unit_long = Sensor::fromName($class);

require 'includes/html/graphs/device/sensor.inc.php';
