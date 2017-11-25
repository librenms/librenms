<?php

$valid['sensor'] = array();

// Pre-cache data for later use
$pre_cache = array();
$pre_cache_file = 'includes/discovery/sensors/pre-cache/' . $device['os'] . '.inc.php';
if (is_file($pre_cache_file)) {
    echo "Pre-cache {$device['os']}: ";
    include $pre_cache_file;
    echo PHP_EOL;
    d_echo($pre_cache);
}

// TODO change to exclude os with pre-cache php file, but just exclude them by hand for now (like avtech)
if (isset($device['dynamic_discovery']['modules']['sensors']) && $device['os'] != 'avtech') {
    foreach ($device['dynamic_discovery']['modules']['sensors'] as $key => $data_array) {
        foreach ($data_array['data'] as $data) {
            foreach ((array)$data['oid'] as $oid) {
                if (!isset($pre_cache[$oid])) {
                    if (isset($data['snmp_flags'])) {
                        $snmp_flag = $data['snmp_flags'];
                    } else {
                        $snmp_flag = '-OeQUs';
                    }
                    $snmp_flag .= ' -Ih';
                    $pre_cache[$oid] = snmpwalk_cache_oid($device, $oid, $pre_cache[$oid], $device['dynamic_discovery']['mib'], null, $snmp_flag);
                }
            }
        }
    }
}

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
    'delay',
    'quality_factor',
    'chromatic_dispersion',
    'ber',
);
sensors($run_sensors, $device, $valid, $pre_cache);
unset(
    $pre_cache,
    $run_sensors,
    $entitysensor
);
