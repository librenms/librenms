#!/usr/bin/env php
<?php

/*
 * Configuration to JSON converter
 * Written by Job Snijders <job@instituut.net>
 *
 */

$init_modules = array();
require __DIR__ . '/includes/init.php';

if (isCli()) {
    global $config;
    echo json_encode($config);
}
