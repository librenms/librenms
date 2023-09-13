#!/usr/bin/env php
<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

use LibreNMS\Util\Debug;

$init_modules = ['discovery'];
require __DIR__ . '/includes/init.php';

$start = microtime(true);
Log::setDefaultDriver('console');
$sqlparams = [];
$options = getopt('h:m:i:n:d::v::a::q', ['os:', 'type:', 'device-groups:']);

if (! isset($options['q'])) {
    echo \LibreNMS\Config::get('project_name') . " Discovery\n";
}

if (isset($options['h'])) {
    if ($options['h'] == 'odd') {
        $options['n'] = '1';
        $options['i'] = '2';
    } elseif ($options['h'] == 'even') {
        $options['n'] = '0';
        $options['i'] = '2';
    } elseif ($options['h'] == 'all') {
        $where = ' ';
        $doing = 'all';
    } elseif ($options['h'] == 'new') {
        $new_discovery_lock = Cache::lock('new-discovery', 300);
        $where = 'AND devices.last_discovered IS NULL';
        $doing = 'new';
    } elseif ($options['h']) {
        if (is_numeric($options['h'])) {
            $where = "AND devices.device_id = '" . $options['h'] . "'";
            $doing = $options['h'];
        } else {
            $where = "AND devices.hostname LIKE '" . str_replace('*', '%', $options['h']) . "'";
            $doing = $options['h'];
        }
    }//end if
}//end if

if (isset($options['os'])) {
    $where .= ' AND os = ?';
    $sqlparams[] = $options['os'];
}

if (isset($options['type'])) {
    $where .= ' AND type = ?';
    $sqlparams[] = $options['type'];
}

if (isset($options['device-groups'])) {
    $cleaned_groups = preg_replace('/^([ ]*,[ ]*|[ ]*,[ ]*)$/', '', trim($options['device-groups']));
    $device_groups = preg_split('/[ ]*,[ ]*/', $cleaned_groups);
    $group_where_items = [];
    foreach ($device_groups as $group) {
        $group = trim($group);
        if (is_numeric($group)) {
            $sqlparams[] = $group;
            $group_where_items[] = 'device_groups.id = ?';
        } else {
            // even add empty values, they will not yield any result
            $sqlparams[] = str_replace('*', '%', $group);
            $group_where_items[] = 'device_groups.name LIKE ?';
        }
    }
    if ($group_where_items) {
        $where .= ' AND (' . implode(' OR ', $group_where_items) . ')';
    }
}

if (isset($options['i']) && $options['i'] && isset($options['n'])) {
    $where .= ' AND MOD(devices.device_id, ' . $options['i'] . ") = '" . $options['n'] . "'";
    $doing = $options['n'] . '/' . $options['i'];
}

if (Debug::set(isset($options['d']), false) || isset($options['v'])) {
    echo \LibreNMS\Util\Version::get()->header();

    echo "DEBUG!\n";
    Debug::setVerbose(isset($options['v']));
    \LibreNMS\Util\OS::updateCache(true); // Force update of OS Cache
}

if (! $where) {
    echo "-h <device id> | <device hostname wildcard>\n";
    echo "                   Discover single device\n";
    echo "-h odd             Discover odd numbered devices  (same as -i 2 -n 0)\n";
    echo "-h even            Discover even numbered devices (same as -i 2 -n 1)\n";
    echo "-h all             Discover all devices\n";
    echo "-h new             Discover all devices that have not had a discovery run before\n";
    echo "--os <os_name>     Discover devices only with specified operating system\n";
    echo "--type <type>      Discover devices only with specified type\n";
    echo "-m                 Specify single module to be run. Comma separate modules, submodules may be added with /\n";
    echo "--device-groups <group id/s or name/s or name part/s)>\n";
    echo "                   Discover only devices having at least one of the given device groups\n";
    echo "                   Multiple groups may be separated by comma\n";
    echo "-i <instances> -n <number>\n";
    echo "                   Discover as instance <number> of <instances>\n";
    echo "                   Instances start at 0. 0-3 for -n 4\n";
    echo "\n";
    echo "Debugging and testing options:\n";
    echo "-d                 Enable debugging output\n";
    echo "-v                 Enable verbose debugging output\n";
    echo "\n";
    echo "Invalid arguments!\n";
    exit;
}

// If we've specified modules with -m, use them
$module_override = parse_modules('discovery', $options);

$discovered_devices = 0;

if (! empty(\LibreNMS\Config::get('distributed_poller_group'))) {
    $where .= ' AND devices.poller_group IN(' . \LibreNMS\Config::get('distributed_poller_group') . ')';
}

$sql = 'SELECT DISTINCT devices.* FROM `devices` ' .
       'LEFT JOIN device_group_device ON devices.device_id = device_group_device.device_id ' .
       'LEFT JOIN device_groups ON device_group_device.device_group_id = device_groups.id ' .
       "WHERE devices.disabled = 0 $where ORDER BY devices.device_id DESC";

global $device;
foreach (dbFetch($sql, $sqlparams) as $device) {
    $device_start = microtime(true);
    DeviceCache::setPrimary($device['device_id']);

    if (discover_device($device, $module_override)) {
        $discovered_devices++;

        $device_time = round(microtime(true) - $device_start, 3);
        DB::table('devices')->where('device_id', $device['device_id'])->update([
            'last_discovered_timetaken' => $device_time,
            'last_discovered' => DB::raw('NOW()'),
        ]);

        echo "Discovered in $device_time seconds\n\n";
    }
}

$end = microtime(true);
$run = ($end - $start);
$proctime = substr($run, 0, 5);

if (isset($new_discovery_lock)) {
    $new_discovery_lock->release();
}

$string = $argv[0] . " $doing " . date(\LibreNMS\Config::get('dateformat.compact')) . " - $discovered_devices devices discovered in $proctime secs";
d_echo("$string\n");

if (! isset($options['q'])) {
    echo PHP_EOL;
    app(\App\Polling\Measure\MeasurementManager::class)->printStats();
}

logfile($string);

if ($doing !== 'new' && $discovered_devices == 0) {
    // No discoverable devices, either down or disabled
    exit(5);
}
