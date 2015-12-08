#!/usr/bin/env php
<?php

require 'includes/defaults.inc.php';
require 'config.php';
require 'includes/definitions.inc.php';
require 'includes/functions.php';
rrdtool_pipe_open($rrd_process, $rrd_pipes);

$options = getopt('h:p:');

$hosts = str_replace('*', '%', mres($options['h']));
$ports = str_replace('*', '%', mres($options['p']));

if (empty($hosts) && empty($ports)) {

    echo "-h <device hostname wildcard>    Device(s) to match\n";
    echo "-p <ifName widcard>              Port(s) to match using ifName\n";
    echo "\n";

}

foreach (dbFetchRows("SELECT `device_id`,`hostname` FROM `devices` WHERE `hostname` LIKE ?", array('%'.$hosts.'%')) as $device) {
    echo "Found hostname " . $device['hostname'].".......\n";
    foreach (dbFetchRows("SELECT `ifIndex`,`ifName`,`ifSpeed` FROM `ports` WHERE `ifName` LIKE ? AND `device_id` = ?", array('%'.$ports.'%',$device['device_id'])) as $port) {
        echo "Tuning port " . $port['ifName'].".......\n";
        $host_rrd = $config['rrd_dir'].'/'.$device['hostname'];
        $rrdfile = $host_rrd.'/port-'.safename($port['ifIndex'].'.rrd');
        rrdtool_tune('port',$rrdfile,$port['ifSpeed']);
    }
}

