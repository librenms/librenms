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

if (isset($device['dynamic_discovery']['modules']['sensors'])) {
    foreach ($device['dynamic_discovery']['modules']['sensors'] as $key => $data_array) {
        foreach ($data_array['data'] as $data) {
            foreach ((array)$data['oid'] as $oid) {
                $tmp_name = $oid;
                if (!isset($pre_cache[$tmp_name])) {
                    if (isset($data['snmp_flags'])) {
                        $snmp_flag = $data['snmp_flags'];
                    } else {
                        $snmp_flag = '-OeQUs';
                    }
                    $snmp_flag .= ' -Ih';
                    if ($key === 'pre-cache') {
                        $array_data = '__cached';
                    } else {
                        $array_data = $tmp_name;
                    }
                    $pre_cache[$array_data] = snmpwalk_cache_oid($device, $oid, $pre_cache[$array_data], $device['dynamic_discovery']['mib'], null, $snmp_flag);
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
);
sensors($run_sensors, $device, $valid, $pre_cache);
unset(
    $pre_cache,
    $run_sensors,
    $entitysensor
);
