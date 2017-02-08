<?php

$valid['sensor'] = array();

// Pre-cache data for later use
require 'includes/discovery/sensors/pre-cache.inc.php';

// Run custom sensors 
require 'includes/discovery/sensors/cisco-entity-sensor.inc.php';
require 'includes/discovery/sensors/entity-sensor.inc.php';
require 'includes/discovery/sensors/ipmi.inc.php';

if ($device['os'] == 'netscaler') {
    include 'includes/discovery/sensors/netscaler.inc.php';
}

if ($device['os'] == 'openbsd') {
    include 'includes/discovery/sensors/openbsd.inc.php';
}

if (strstr($device['hardware'], 'Dell')) {
    include 'includes/discovery/sensors/fanspeeds/dell.inc.php';
    include 'includes/discovery/sensors/states/dell.inc.php';
    include 'includes/discovery/sensors/temperatures/dell.inc.php';
}

$run_sensors = array(
    'airflow',
    'current',
    'charge',
    'dbm',
    'fanspeeds',
    'frequencies',
    'humidity',
    'load',
    'power',
    'runtime',
    'signal',
    'states',
    'temperatures',
    'voltages',
);
sensors($run_sensors, $device, $valid, $pre_cache);
unset(
    $pre_cache,
    $run_sensors,
    $entitysensor
);
