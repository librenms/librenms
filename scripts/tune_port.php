#!/usr/bin/env php
<?php

$init_modules = array();
require realpath(__DIR__ . '/..') . '/includes/init.php';

rrdtool_initialize();

$options = getopt('h:p:');

$hosts = str_replace('*', '%', mres($options['h']));
$ports = str_replace('*', '%', mres($options['p']));

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

foreach (dbFetchRows("SELECT `device_id`,`hostname` FROM `devices` WHERE `hostname` LIKE ?", array('%'.$hosts.'%')) as $device) {
    echo "Found hostname " . $device['hostname'].".......\n";
    foreach (dbFetchRows("SELECT `port_id`,`ifIndex`,`ifName`,`ifSpeed` FROM `ports` WHERE `ifName` LIKE ? AND `device_id` = ?", array('%'.$ports.'%',$device['device_id'])) as $port) {
        echo "Tuning port " . $port['ifName'].".......\n";
        $rrdfile = get_port_rrdfile_path($device['hostname'], $port['port_id']);
        rrdtool_tune('port', $rrdfile, $port['ifSpeed']);
    }
}

rrdtool_close();
