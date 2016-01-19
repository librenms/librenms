<?php

$valid['sensor'] = array();

echo 'Sensors: ';

require 'includes/discovery/sensors/cisco-entity-sensor.inc.php';
require 'includes/discovery/sensors/entity-sensor.inc.php';
require 'includes/discovery/sensors/ipmi.inc.php';

if ($device['os'] == 'netscaler') {
    include 'includes/discovery/sensors/netscaler.inc.php';
}

if ($device['os'] == 'openbsd') {
    include 'includes/discovery/sensors/openbsd.inc.php';
}

require 'includes/discovery/sensors/temperatures.inc.php';
require 'includes/discovery/sensors/humidity.inc.php';
require 'includes/discovery/sensors/voltages.inc.php';
require 'includes/discovery/sensors/frequencies.inc.php';
require 'includes/discovery/sensors/current.inc.php';
require 'includes/discovery/sensors/power.inc.php';
require 'includes/discovery/sensors/fanspeeds.inc.php';
require 'includes/discovery/sensors/charge.inc.php';
require 'includes/discovery/sensors/load.inc.php';
require 'includes/discovery/sensors/states.inc.php';
