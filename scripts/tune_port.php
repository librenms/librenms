#!/usr/bin/env php
<?php

$init_modules = [];
require realpath(__DIR__ . '/..') . '/includes/init.php';

$options = getopt('h:p:');

$hosts = str_replace('*', '%', $options['h']);
$ports = str_replace('*', '%', $options['p']);

if (empty($hosts) && empty($ports)) {
    echo "-h <device hostname wildcard>    Device(s) to match (all is a valid arg)\n";
    echo "-p <ifName widcard>              Port(s) to match using ifName (all is a valid arg)\n";
    echo "\n";
    exit;
}

if ($hosts == 'all') {
    $hosts = '';
}
if ($ports == 'all') {
    $ports = '';
}

foreach (dbFetchRows('SELECT `device_id`,`hostname` FROM `devices` WHERE `hostname` LIKE ?', ['%' . $hosts . '%']) as $device) {
    echo 'Found hostname ' . $device['hostname'] . ".......\n";
    foreach (dbFetchRows('SELECT `port_id`,`ifIndex`,`ifName`,`ifSpeed` FROM `ports` WHERE `ifName` LIKE ? AND `device_id` = ?', ['%' . $ports . '%', $device['device_id']]) as $port) {
        echo 'Tuning port ' . $port['ifName'] . ".......\n";
        $rrdfile = get_port_rrdfile_path($device['hostname'], $port['port_id']);
        Rrd::tune('port', $rrdfile, $port['ifSpeed']);
    }
}

Rrd::close();
