#!/usr/bin/env php
<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    LibreNMS
 * @subpackage snmptraps
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 * @copyright  (C) 2018 LibreNMS
 * Adapted from old snmptrap.php handler
 */

chdir(__DIR__); // cwd to the directory containing this script

$init_modules = array();
require __DIR__ . '/includes/init.php';

$options = getopt('d::');

if (set_debug(isset($options['d']))) {
    echo "DEBUG!\n";
}

// Creates an array with trap info
while ($f = fgets(STDIN)) {
    $entry[] = $f;
}

// Formatting array
$hostname = trim($entry['0']);
$ip = str_replace(array("UDP:","[","]"), "", $entry['1']);
$ip = trim(strstr($ip, ":", true));
$oid = trim(strstr($entry['3'], " "));
$oid = str_replace("::", "", strstr($oid, "::"));
$who = trim(strstr($entry['4'], " "));

$device = @dbFetchRow('SELECT * FROM devices WHERE `hostname` = ?', [$hostname]);

if (!$device['device_id']) {
    $device = @dbFetchRow('SELECT * FROM ipv4_addresses AS A, ports AS I WHERE A.ipv4_address = ? AND I.port_id = A.port_id', [$ip]);
}

if (!$device['device_id']) {
    echo "unknown device\n";
    exit;
}

$file = $config['install_dir'].'/includes/snmptrap/'.$oid.'.inc.php';
if (is_file($file)) {
    include "$file";
} else {
    echo "unknown trap ($file)";
}
