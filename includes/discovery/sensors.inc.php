<?php

$valid['sensor'] = array();

echo 'Sensors: ';

require 'includes/discovery/cisco-entity-sensor.inc.php';
require 'includes/discovery/entity-sensor.inc.php';
require 'includes/discovery/ipmi.inc.php';

if ($device['os'] == 'netscaler') {
    include 'includes/discovery/sensors-netscaler.inc.php';
}

if ($device['os'] == 'openbsd') {
    include 'includes/discovery/sensors-openbsd.inc.php';
}

require 'includes/discovery/temperatures.inc.php';
require 'includes/discovery/humidity.inc.php';
require 'includes/discovery/voltages.inc.php';
require 'includes/discovery/frequencies.inc.php';
require 'includes/discovery/current.inc.php';
require 'includes/discovery/power.inc.php';
require 'includes/discovery/fanspeeds.inc.php';
require 'includes/discovery/charge.inc.php';
require 'includes/discovery/load.inc.php';
require 'includes/discovery/states.inc.php';
