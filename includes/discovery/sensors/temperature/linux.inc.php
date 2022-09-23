<?php
/*
 * cpu temp for raspberry pi
 * requires snmp extend agent script from librenms-agent
 */

use Illuminate\Support\Str;
use LibreNMS\Config;

$sensor_oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.9.114.97.115.112.98.101.114.114.121.1';
$value = snmp_get($device, $sensor_oid, '-Oqve');
$value = trim($value, '"');
if (is_numeric($value)) {
    $sensor_type = 'raspberry_temp';
    $descr = 'CPU Temp';
    discover_sensor($valid['sensor'], 'temperature', $device, $sensor_oid, 1, $sensor_type, $descr, 1, 1, null, null, null, null, $value);
}

if (Str::startsWith($device['sysObjectID'], '.1.3.6.1.4.1.232.')) {
    echo 'HP_ILO ';
    $oids = snmp_walk($device, '.1.3.6.1.4.1.232.6.2.6.8.1.2.1', '-Osqn', '');
    $oids = trim($oids);
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data != '') {
            [$oid] = explode(' ', $data);
            $split_oid = explode('.', $oid);
            $temperature_id = $split_oid[(count($split_oid) - 2)] . '.' . $split_oid[(count($split_oid) - 1)];

            $descr_oid = ".1.3.6.1.4.1.232.6.2.6.8.1.3.$temperature_id";
            $descr = snmp_get($device, $descr_oid, '-Oqnv', 'CPQHLTH-MIB', 'hp');

            $temperature_oid = ".1.3.6.1.4.1.232.6.2.6.8.1.4.$temperature_id";
            $temperature = snmp_get($device, $temperature_oid, '-Oqv', '');

            $threshold_oid = ".1.3.6.1.4.1.232.6.2.6.8.1.5.$temperature_id";
            $threshold = snmp_get($device, $threshold_oid, '-Oqv', '');

            if (! empty($temperature)) {
                discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $oid, 'hpilo', $descr, '1', '1', null, null, null, $threshold, $temperature);
            }
        }
    }
}

if (preg_match('/(Linux).+(ntc)/', $device['sysDescr'])) {
    $sensor_type = 'chip_axp209_temperature';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.10.112.111.119.101.114.45.115.116.97.';
    $lowlimit = -40;
    $lowwarnlimit = -35;
    $warnlimit = 120;
    $limit = 130;
    $descr = 'AXP209 Temperature';
    $index = '116.1';
    $value = snmp_get($device, $oid . $index, '-Oqv');
    if (is_numeric($value)) {
        discover_sensor($valid['sensor'], 'temperature', $device, $oid . $index, $index, $sensor_type, $descr, '1', '1', $lowlimit, $lowwarnlimit, $warnlimit, $limit, $value);
    }
}

include_once Config::get('install_dir') . '/includes/discovery/sensors/temperature/supermicro.inc.php';
