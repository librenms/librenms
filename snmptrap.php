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
require __DIR__ . '/includes/snmptrap.inc.php';

$options = getopt('d::');

if (set_debug(isset($options['d']))) {
    echo "DEBUG!\n";
}

// Creates an array with trap info
while ($f = fgets(STDIN)) {
    $entry[] = $f;
}

//Format hostname and ip from received trap
$hostname = trim($entry[0]);
$ip = str_replace(array("UDP:","[","]"), "", $entry[1]);
$ip = trim(strstr($ip, ":", true));

$device = dbFetchRow('SELECT * FROM devices WHERE `hostname`=? OR `hostname`=? OR `ip`=?', [$hostname, $ip, inet_pton($ip)]);

if (empty($device)) {
    $device = dbFetchRow('SELECT * FROM ipv4_addresses AS A, ports AS I WHERE A.ipv4_address = ? AND I.port_id = A.port_id', [$ip]);
}

if (empty($device)) {
    echo "unknown device\n";
    exit;
}


process_trap($device, $entry);
