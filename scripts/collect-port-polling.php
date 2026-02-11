#!/usr/bin/env php
<?php

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Number;

$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

$init_modules = [];
require $install_dir . '/includes/init.php';
$options = getopt('dh:e:', ['help']);

LibrenmsConfig::set('rrd.enable', false);
LibrenmsConfig::set('influxdb.enable', false);
LibrenmsConfig::set('influxdbv2.enable', false);
LibrenmsConfig::set('nographite', true);
LibrenmsConfig::set('kafka.enable', false);

function print_help()
{
    echo "-h <device id> | <device hostname wildcard>  Poll single device, wildcard hostname, or comma separated list\n";
    echo "-e <percentage>                              Enable/disable selected ports polling for devices which would benefit <percentage> from a change\n";
    echo "\n";
}

if (isset($options['d'])) {
    Debug::set();
}

if (isset($options['help'])) {
    print_help();
    exit(0);
}

$devices = Device::query()->isActive()->orderBy('hostname', 'ASC');
if (isset($options['h'])) {
    if (is_numeric($options['h'])) {
        $devices->where('device_id', $options['h']);
    } elseif (Str::contains($options['h'], ',')) {
        $device_ids = array_map(trim(...), explode(',', $options['h']));
        $device_ids = array_filter($device_ids, is_numeric(...));
        $devices->whereIn('device_id', $device_ids);
    } else {
        $devices->where('hostname', 'like', str_replace('*', '%', $options['h']));
    }
}

$devices = $devices->withCount([
    'ports',
    'ports as inactive_count' => fn ($q) => $q->where('deleted', 1)->orWhere('ifAdminStatus', '!=', 'up')->orWhere('disabled', 1),
]);

$devices = $devices->get()->toArray();

if (isset($options['e'])) {
    if (! is_numeric($options['e']) || $options['e'] < 0) {
        print_help();
        exit(1);
    }
    $enable_sel_value = $options['e'];
}

echo 'Full Polling: ';
LibrenmsConfig::set('polling.selected_ports', false);
foreach ($devices as $index => $device) {
    echo $device['device_id'] . ' ';
    if (! Debug::isEnabled()) {
        ob_start();
    }

    $port_test_start = microtime(true);
    include $install_dir . '/includes/polling/ports.inc.php';
    $devices[$index]['full_time_sec'] = microtime(true) - $port_test_start;
    ob_end_clean();
}
echo PHP_EOL;

LibrenmsConfig::set('polling.selected_ports', true);
echo 'Selective Polling: ';
foreach ($devices as $index => $device) {
    echo $device['device_id'] . ' ';
    if (! Debug::isEnabled()) {
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
    $device['inactive_ratio'] = ($device['inactive_count'] == 0 ? 0 : ($device['inactive_count'] / $device['ports_count']));
    $device['diff_sec'] = $device['selective_time_sec'] - $device['full_time_sec'];
    $device['diff_perc'] = Number::calculatePercent($device['diff_sec'], $device['full_time_sec']);

    // $enable_sel_value is negative and we want to enable it for all devices with an even lower value.
    // It also has to save more than 1 s, or we might enable it for devices with i.e. 100ms vs 50ms, which isn't needed.
    $device['set'] = 'none';
    if (isset($enable_sel_value) && $device['diff_perc'] < ($enable_sel_value * -1) && $device['diff_sec'] < -1) {
        set_dev_attrib($device, 'selected_ports', 'true');
        $device['set'] = 'true';
        $set_count++;
    }
    if (isset($enable_sel_value) && $device['diff_perc'] > $enable_sel_value && $device['diff_sec'] > 1) {
        set_dev_attrib($device, 'selected_ports', 'false');
        $device['set'] = 'false';
        $set_count++;
    }
}
unset($device);  // will edit the wrong thing after using $device by reference

// print out the results
$stats = [
    'device_id',
    'os',
    'port_count',
    'inactive_ratio',
    'full_time',
    'selective_time',
    'diff',
    'diff',
    'set',
];

echo PHP_EOL;
$header = "| %9.9s | %-11.11s | %10.10s | %14.14s | %10.10s | %14.14s | %8.10s | %5.9s | %5.5s |\n";
call_user_func_array(printf(...), array_merge([$header], $stats));

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
$difference_perc = Number::calculatePercent($difference, $total_full_time);
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
