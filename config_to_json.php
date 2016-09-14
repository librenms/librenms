#!/usr/bin/env php
<?php

/*
 * Configuration to JSON converter
 * Written by Job Snijders <job@instituut.net>
 *
 */

chdir(__DIR__); // cwd to the directory containing this script

// check if we are running through the CLI, otherwise abort
if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
    include_once 'includes/defaults.inc.php';
    include_once 'config.php';
    echo json_encode($config);
}
