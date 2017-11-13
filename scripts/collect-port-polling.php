#!/usr/bin/env php
<?php

use LibreNMS\Config;

$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

$init_modules = array();
require $install_dir . '/includes/init.php';
$options = getopt('dh:e:', array("help"));

Config::set('norrd', true);
Config::set('noinfluxdb', true);
Config::set('nographite', true);

function print_help()
{
    echo "-h <device id> | <device hostname wildcard>  Poll single device, wildcard hostname, or comma separated list\n";
    echo "-e <percentage>                              Enable/disable selected ports polling for devices which would benefit <percentage> from a change\n";
    echo "\n";
}

if (isset($options['d'])) {
    $debug = true;
}

if (isset($options['help'])) {
    print_help();
    exit(0);
}

if (isset($options['h'])) {
    if (is_numeric($options['h'])) {
        $where = "AND `device_id` = ?";
        $params = array($options['h']);
    } elseif (str_contains($options['h'], ',')) {
        $device_ids = array_map('trim', explode(',', $options['h']));
        $device_ids = array_filter($device_ids, 'is_numeric');
        $where = 'AND `device_id` in ' . dbGenPlaceholders(count($device_ids));
        $params = $device_ids;
    } else {
        $where = "AND `hostname` LIKE ?";
        $params = array(str_replace('*', '%', mres($options['h'])));
    }
    $devices = dbFetch("SELECT * FROM `devices` WHERE status = 1 AND disabled = 0 $where ORDER BY `hostname` ASC", $params);
} else {
    $devices = get_all_devices();
}

if (isset($options['e'])) {
    if (!is_numeric($options['e']) || $options['e'] < 0) {
        print_help();
        exit(1);
    }
    $enable_sel_value = $options['e'];
}

echo "Full Polling: ";
Config::set('polling.selected_ports', false);
foreach ($devices as $index => $device) {
    echo $device['device_id'] . ' ';
    if (!$debug) {
        ob_start();
    }

    $port_test_start = microtime(true);
    include $install_dir . '/includes/polling/ports.inc.php';
    $devices[$index]['full_time_sec'] = microtime(true) - $port_test_start;
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
    $devices[$index]['selective_time_sec'] = microtime(true) - $port_test_start;
    ob_end_clean();
}
echo PHP_EOL;


// collect port counts
$inactive_sql = "`deleted` = 1 OR `ifAdminStatus` != 'up' OR `disabled` = 1";
$set_count = 0;
foreach ($devices as &$device) {
    $count = dbFetchCell('SELECT COUNT(*) FROM `ports` WHERE `device_id`=?', array($device['device_id']));
    $inactive = dbFetchCell(
        "SELECT COUNT(*) FROM `ports` WHERE `device_id`=? AND ($inactive_sql)",
        array($device['device_id'])
    );

    $device['port_count'] = $count;
    $device['inactive_ratio'] = ($inactive == 0 ? 0 : ($inactive / $count));
    $device['diff_sec'] = $device['selective_time_sec'] - $device['full_time_sec'];
    $device['diff_perc'] = ($device['diff_sec'] / $device['full_time_sec']) * 100;

    // $enable_sel_value is negative and we want to enable it for all devices with an even lower value.
    // It also has to save more than 1 s, or we might enable it for devices with i.e. 100ms vs 50ms, which isn't needed.
    $device['set'] = "none";
    if (isset($enable_sel_value) && $device['diff_perc'] < ($enable_sel_value * -1) && $device['diff_sec'] < -1) {
        set_dev_attrib($device, 'selected_ports', "true");
        $device['set'] = "true";
        $set_count++;
    }
    if (isset($enable_sel_value) && $device['diff_perc'] > $enable_sel_value && $device['diff_sec'] > 1) {
        set_dev_attrib($device, 'selected_ports', "false");
        $device['set'] = "false";
        $set_count++;
    }
}
unset($device);  // will edit the wrong thing after using $device by reference

// print out the results
$stats = array(
    'device_id',
    'os',
    'port_count',
    'inactive_ratio',
    'full_time',
    'selective_time',
    'diff',
    'diff',
    'set'
);

echo PHP_EOL;
$header = "| %9.9s | %-11.11s | %10.10s | %14.14s | %10.10s | %14.14s | %8.10s | %5.9s | %5.5s |\n";
call_user_func_array('printf', array_merge(array($header), $stats));

$mask = "| %9.9s | %-11.11s | %10.10s | %14.3f | %9.3fs | %13.3fs | %s%+7.3fs\e[0m | %s%+4.0f%%\e[0m | %5.5s |\n";
foreach ($devices as $device) {
    $diff_color = ($device['diff_sec'] > 0 ? "\033[0;31m" : "\033[0;32m");
    printf(
        $mask,
        $device['device_id'],
        $device['os'],
        $device['port_count'],
        $device['inactive_ratio'],
        $device['full_time_sec'],
        $device['selective_time_sec'],
        $diff_color,
        $device['diff_sec'],
        $diff_color,
        $device['diff_perc'],
        $device['set']
    );
}

$total_ports = array_sum(array_column($devices, 'port_count'));
$inactive_ratio = array_sum(array_column($devices, 'inactive_ratio')) / count($devices);
$total_full_time = array_sum(array_column($devices, 'full_time_sec'));
$total_selective_time = array_sum(array_column($devices, 'selective_time_sec'));
$difference = $total_selective_time - $total_full_time;
$difference_perc = ($difference / $total_full_time) * 100;
$total_diff_color = ($difference > 0 ? "\033[0;31m" : "\033[0;32m");

printf(
    $mask,
    'Totals:',
    '',
    $total_ports,
    $inactive_ratio,
    $total_full_time,
    $total_selective_time,
    $total_diff_color,
    $difference,
    $total_diff_color,
    $difference_perc,
    $set_count
);
