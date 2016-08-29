#!/usr/bin/env php
<?php

/*
 * LibreNMS module to poll Nagios Services
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

chdir(dirname($argv[0]));

require 'includes/defaults.inc.php';
require 'config.php';
require 'includes/definitions.inc.php';
require 'includes/functions.php';

$options = getopt('d::');
if (isset($options['d'])) {
    echo "DEBUG!\n";
    $debug = true;
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_reporting', 1);
} else {
    $debug = false;
    // ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 0);
    // ini_set('error_reporting', 0);
}

if (isset($options['f'])) {
    $config['noinfluxdb'] = true;
}

if ($config['noinfluxdb'] !== true && $config['influxdb']['enable'] === true) {
    $influxdb = influxdb_connect();
} else {
    $influxdb = false;
}

rrdtool_initialize();

foreach (dbFetchRows('SELECT * FROM `devices` AS D, `services` AS S WHERE S.device_id = D.device_id ORDER by D.device_id DESC') as $service) {
    // Run the polling function
    poll_service($service);
} //end foreach
rrdtool_close();
