#!/usr/bin/env php
<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$init_modules = array();
require realpath(__DIR__ . '/..') . '/includes/init.php';

// Handle options
$options = getopt('ght::x::');

if (isset($options['h'])) {
    echo "gen_smokeping.php\n";
    echo "-t<title> Include the LibreNMS items in a titled subsection rather than";
    echo "          at root level - previously this was manually added on per\n";
    echo "          the documentation. Default title with empty -t is LibreNMS\n";
    echo "-g Use device groups instead of types to build the menus (experimental,\n";
    echo "   works fine in SmokePing but LibreNMS won't be able to find the RRDs)\n";
    echo "-x<excl,excl...> Comma separated list of any group or type names to exclude\n";
    echo "-h Show this usage info\n";
    echo "\n";
    exit;
}

$groups = isset($options['g']);

if (isset($options['t'])) {
    if ($options['t']) {
        $toptitle = $options['t'];
    } else {
        $toptitle = 'LibreNMS';
    }
} else {
    $toptitle = false;
}

// Build SQL bits for any exclusions
if (isset($options['x'])) {
    echo $options['x'];
    $exclude = array_map('trim', explode(',', $options['x']));
    if ($groups) {
        $excludeSQL = ' WHERE `name` NOT IN (' . implode(',', array_fill(0, count($exclude), '?')) . ')';
    } else {
        $excludeSQL = ' AND `type` NOT IN (' . implode(',', array_fill(0, count($exclude), '?')) . ')';
    }
} else {
    $exclude = array();
    $excludeSQL = '';
}

// Add an extra + to everything if we are doing this as a subsection
$pref = $toptitle  ? '+' : '';

if ($toptitle) {
    echo "$pref $toptitle" . PHP_EOL;
    echo "menu = " . ($toptitle ? $toptitle : 'LibreNMS') . PHP_EOL;
    echo "title = Network Latency Grapher for devices imported from LibreNMS" . PHP_EOL;
}

if ($groups) {
    // By device group
    foreach (dbFetchRows("SELECT id, `name`, COALESCE(`desc`, `name`) AS `desc` FROM device_groups$excludeSQL ORDER BY `name`", $exclude) as $groups) {
        // Replace any chars not allowed by Smokeping at this level
        echo "$pref+ " . preg_replace('/[^-_0-9a-zA-Z]/', '_', $groups['name']) . PHP_EOL;
        echo 'menu = ' . $groups['name'] . PHP_EOL;
        echo 'title = ' . $groups['desc'] . PHP_EOL;
        foreach (dbFetchRows("SELECT hostname FROM devices JOIN device_group_device dgd ON dgd.device_group_id = ? AND dgd.device_id = devices.device_id ORDER BY hostname", array($groups['id'])) as $devices) {
            echo "$pref++ " . str_replace(['.', ' '], '_', $devices['hostname']) . PHP_EOL;
            echo 'menu = ' . $devices['hostname'] . PHP_EOL;
            echo 'title = ' . $devices['hostname'] . PHP_EOL;
            echo 'host = ' . $devices['hostname'] . PHP_EOL . PHP_EOL;
        }
    }
} else {
    // By device type
    foreach (dbFetchRows("SELECT `type` FROM `devices` WHERE `disabled` = 0 AND `type` != ''$excludeSQL GROUP BY `type`", $exclude) as $groups) {
        // Replace any chars not allowed by Smokeping at this level
        echo "$pref+ " . preg_replace('/[^-_0-9a-zA-Z]/', '_', $groups['type']) . PHP_EOL;
        echo 'menu = ' . $groups['type'] . PHP_EOL;
        echo 'title = ' . $groups['type'] . PHP_EOL;
        foreach (dbFetchRows("SELECT `hostname` FROM `devices` WHERE `type` = ? AND `disabled` = 0", array($groups['type'])) as $devices) {
            echo "$pref++ " . str_replace(['.', ' '], '_', $devices['hostname']) . PHP_EOL;
            echo 'menu = ' . $devices['hostname'] . PHP_EOL;
            echo 'title = ' . $devices['hostname'] . PHP_EOL;
            echo 'host = ' . $devices['hostname'] . PHP_EOL . PHP_EOL;
        }
    }
}
