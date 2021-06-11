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
$sqlparams = [];
$options = getopt('h:m:i:n:d::v::a::q', ['os:', 'type:']);

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
        $where = 'AND `last_discovered` IS NULL';
        $doing = 'new';
    } elseif ($options['h']) {
        if (is_numeric($options['h'])) {
            $where = "AND `device_id` = '" . $options['h'] . "'";
            $doing = $options['h'];
        } else {
            $where = "AND `hostname` LIKE '" . str_replace('*', '%', $options['h']) . "'";
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

if (isset($options['i']) && $options['i'] && isset($options['n'])) {
    $where .= ' AND MOD(device_id,' . $options['i'] . ") = '" . $options['n'] . "'";
    $doing = $options['n'] . '/' . $options['i'];
}

if (Debug::set(isset($options['d']), false) || isset($options['v'])) {
    $versions = version_info();
    echo <<<EOH
===================================
Version info:
Commit SHA: {$versions['local_sha']}
Commit Date: {$versions['local_date']}
DB Schema: {$versions['db_schema']}
PHP: {$versions['php_ver']}
MySQL: {$versions['mysql_ver']}
RRDTool: {$versions['rrdtool_ver']}
SNMP: {$versions['netsnmp_ver']}
==================================
EOH;

    echo "DEBUG!\n";
    Debug::setVerbose(isset($options['v']));
    \LibreNMS\Util\OS::updateCache(true); // Force update of OS Cache
}

if (! $where) {
    echo "-h <device id> | <device hostname wildcard>  Poll single device\n";
    echo "-h odd             Poll odd numbered devices  (same as -i 2 -n 0)\n";
    echo "-h even            Poll even numbered devices (same as -i 2 -n 1)\n";
    echo "-h all             Poll all devices\n";
    echo "-h new             Poll all devices that have not had a discovery run before\n";
    echo "--os <os_name>     Poll devices only with specified operating system\n";
    echo "--type <type>      Poll devices only with specified type\n";
    echo "-i <instances> -n <number>                   Poll as instance <number> of <instances>\n";
    echo "                   Instances start at 0. 0-3 for -n 4\n";
    echo "\n";
    echo "Debugging and testing options:\n";
    echo "-d                 Enable debugging output\n";
    echo "-v                 Enable verbose debugging output\n";
    echo "-m                 Specify single module to be run. Comma separate modules, submodules may be added with /\n";
    echo "\n";
    echo "Invalid arguments!\n";
    exit;
}

// If we've specified modules with -m, use them
$module_override = parse_modules('discovery', $options);

$discovered_devices = 0;

if (! empty(\LibreNMS\Config::get('distributed_poller_group'))) {
    $where .= ' AND poller_group IN(' . \LibreNMS\Config::get('distributed_poller_group') . ')';
}

global $device;
foreach (dbFetch("SELECT * FROM `devices` WHERE disabled = 0 AND snmp_disable = 0 $where ORDER BY device_id DESC", $sqlparams) as $device) {
    DeviceCache::setPrimary($device['device_id']);
    $discovered_devices += (int) discover_device($device, $module_override);
}

$end = microtime(true);
$run = ($end - $start);
$proctime = substr($run, 0, 5);

if ($discovered_devices) {
    if ($doing === 'new') {
        // We have added a new device by this point so we might want to do some other work
        oxidized_reload_nodes();
    }
}

if (isset($new_discovery_lock)) {
    $new_discovery_lock->release();
}

$string = $argv[0] . " $doing " . date(\LibreNMS\Config::get('dateformat.compact')) . " - $discovered_devices devices discovered in $proctime secs";
d_echo("$string\n");

if (! isset($options['q'])) {
    printStats();
}

logfile($string);

if ($doing !== 'new' && $discovered_devices == 0) {
    // No discoverable devices, either down or disabled
    exit(5);
}
