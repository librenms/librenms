#!/usr/bin/env php
<?php

/*
 * Configuration to JSON converter
 * Written by Job Snijders <job@instituut.net>
 *
 */

use LibreNMS\Config;

$init_modules = array('nodb');
require __DIR__ . '/includes/init.php';

if (isCli()) {
    global $config;

    try {
        Config::loadFromDatabase();
    } catch (\LibreNMS\Exceptions\DatabaseConnectException $e) {
        // could not populate db data, still loaded non-db config
    }

    echo json_encode($config);
}
