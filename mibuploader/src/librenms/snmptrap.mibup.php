#!/usr/bin/env php
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_reporting', E_ALL);

require 'includes/defaults.inc.php';
require 'config.php';
require 'includes/definitions.inc.php';
require 'includes/functions.php';

$entry = explode(',', $argv[1], 3);

$sDevName = $entry['0'];

$device = @dbFetchRow('SELECT * FROM devices WHERE `hostname` = ?', array($sDevName));

if (!$device['device_id']) {
    $device = @dbFetchRow('SELECT * FROM ipv4_addresses AS A, ports AS I WHERE A.ipv4_address = ? AND I.port_id = A.port_id', array($sDevName));
}

if (!$device['device_id']) {
    $device = @dbFetchRow('SELECT * FROM devices WHERE `sysName` = ?', array($sDevName));
}

if (!$device['device_id']) {
    logfile('Device with name ' . $sDevName . ' not found.');
    exit;
}

require 'includes/snmptrap/genericTrap.inc.php';
