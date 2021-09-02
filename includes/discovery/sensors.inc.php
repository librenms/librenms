<?php

use LibreNMS\Config;
use LibreNMS\OS;

$valid['sensor'] = [];

/** @var OS $os */
$pre_cache = $os->preCache();

if ($device['os'] == 'rittal-cmc-iii-pu' || $device['os'] == 'rittal-lcp') {
    include 'includes/discovery/sensors/rittal-cmc-iii-sensors.inc.php';
} else {
    // Run custom sensors
    require 'includes/discovery/sensors/cisco-entity-sensor.inc.php';
    require 'includes/discovery/sensors/entity-sensor.inc.php';
    require 'includes/discovery/sensors/ipmi.inc.php';
}

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

if ($device['os'] == 'gw-eydfa') {
    include 'includes/discovery/sensors/gw-eydfa.inc.php';
}

if ($device['os_group'] == 'printer') {
    include 'includes/discovery/sensors/state/printer.inc.php';
}

$run_sensors = [
    'airflow',
    'current',
    'charge',
    'dbm',
    'fanspeed',
    'frequency',
    'humidity',
    'load',
    'loss',
    'power',
    'power_consumed',
    'power_factor',
    'runtime',
    'signal',
    'state',
    'count',
    'temperature',
    'tv_signal',
    'voltage',
    'snr',
    'pressure',
    'cooling',
    'delay',
    'quality_factor',
    'chromatic_dispersion',
    'ber',
    'eer',
    'waterflow',
    'percent',
];

// filter submodules
$run_sensors = array_intersect($run_sensors, Config::get('discovery_submodules.sensors', $run_sensors));

sensors($run_sensors, $device, $valid, $pre_cache);
unset(
    $pre_cache,
    $run_sensors,
    $entitysensor
);
