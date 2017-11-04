#!/usr/bin/env php
<?php

use LibreNMS\Config;

$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

$init_modules = array();
require $install_dir . '/includes/init.php';

$options = getopt('d');

Config::set('norrd', true);
Config::set('noinfluxdb', true);
Config::set('nographite', true);

if (isset($options['d'])) {
    $debug = true;
}

$devices = get_all_devices();

echo "Full Polling: ";
Config::set('polling.selected_ports', false);
foreach ($devices as $index => $device) {
    echo $device['device_id'] . ' ';
    if (!$debug) {
        ob_start();
    }

    $port_test_start = microtime(true);
    include $install_dir . '/includes/polling/ports.inc.php';
    $devices[$index]['full_time'] = microtime(true) - $port_test_start;
    ob_end_clean();
}
echo PHP_EOL;

Config::set('polling.selected_ports', true);
echo "Selective Polling: ";
foreach ($devices as $index => $device) {
    echo $device['device_id'] . ' ';
    if (!$debug) {
        ob_start();
    }

    $port_test_start = microtime(true);
    include $install_dir . '/includes/polling/ports.inc.php';
    $devices[$index]['selective_time'] = microtime(true) - $port_test_start;
    ob_end_clean();
}
echo PHP_EOL;


// collect port counts
$inactive_sql = "`deleted` = 1 OR `ifAdminStatus` != 'up' OR `disabled` = 1";
foreach ($devices as $index => $device) {
    $count = dbFetchCell('SELECT COUNT(*) FROM `ports` WHERE `device_id`=?', array($device['device_id']));
    $inactive = dbFetchCell(
        "SELECT COUNT(*) FROM `ports` WHERE `device_id`=? AND ($inactive_sql)",
        array($device['device_id'])
    );

    $devices[$index]['port_count'] = $count;
    $devices[$index]['inactive_ratio'] = ($inactive == 0 ? 0 : ($inactive / $count));
    $devices[$index]['difference'] = $device['part_time'] - $device['full_time'];
}


// print out the results
$stats = array(
    'device_id',
    'os',
    'port_count',
    'inactive_ratio',
    'full_time',
    'selective_time',
    'difference',
);

echo PHP_EOL;
$header = "| %9.9s | %-11.11s | %10.10s | %14.14s | %9.9s | %14.14s | %10.10s |\n";
call_user_func_array('printf', array_merge(array($header), $stats));

$mask = "| %9.9s | %-11.11s | %10.10s | %14.3f | %9.3f | %14.3f | %10.3f |\n";
foreach ($devices as $device) {
//    $device_stats = array_values(array_intersect_key($device, array_flip($stats)));

    printf(
        $mask,
        $device['device_id'],
        $device['os'],
        $device['port_count'],
        $device['inactive_ratio'],
        $device['full_time'],
        $device['selective_time'],
        $device['difference']
    );
}

$total_ports = array_sum(array_column($devices, 'port_count'));
$inactive_ratio = array_sum(array_column($devices, 'inactive_ratio')) / count($devices);
$total_full_time = array_sum(array_column($devices, 'full_time'));
$total_selective_time = array_sum(array_column($devices, 'selective_time'));
$difference = $total_selective_time - $total_full_time;

printf($mask, 'Totals:', '', $total_ports, $inactive_ratio, $total_full_time, $total_selective_time, $difference);
