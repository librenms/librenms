#!/usr/bin/env php
<?php

/*
 * Configuration to JSON converter
 * Written by Job Snijders <job@instituut.net>
 *
 */

use LibreNMS\Config;

$init_modules = ['nodb'];
require __DIR__ . '/includes/init.php';

if (isCli()) {
    echo Config::json_encode();
}
