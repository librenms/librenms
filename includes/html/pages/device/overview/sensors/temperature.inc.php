<?php

if (($device['os'] ?? '') === 'sodola') {
    return;
}

$sensor_class = \LibreNMS\Enum\Sensor::Temperature;

require 'includes/html/pages/device/overview/generic/sensor.inc.php';
