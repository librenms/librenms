<?php

use LibreNMS\Device\YamlDiscovery;
use LibreNMS\OS;

$valid['sensor'] = array();

// Pre-cache data for later use
$pre_cache = YamlDiscovery::preCache(OS::make($device)); //FIXME don't double fetch

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
    include 'includes/discovery/sensors/fanspeed/dell.inc.php';
    include 'includes/discovery/sensors/power/dell.inc.php';
    include 'includes/discovery/sensors/voltage/dell.inc.php';
    include 'includes/discovery/sensors/state/dell.inc.php';
    include 'includes/discovery/sensors/temperature/dell.inc.php';
}

if (strstr($device['hardware'], 'ProLiant')) {
    include 'includes/discovery/sensors/state/hp.inc.php';
}

$run_sensors = array(
    'airflow',
    'current',
    'charge',
    'dbm',
    'fanspeed',
    'frequency',
    'humidity',
    'load',
    'power',
    'runtime',
    'signal',
    'state',
    'temperature',
    'voltage',
    'snr',
    'pressure',
    'cooling',
);
sensors($run_sensors, $device, $valid, $pre_cache);
unset(
    $pre_cache,
    $run_sensors,
    $entitysensor
);
