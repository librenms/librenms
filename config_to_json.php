#!/usr/bin/env php
<?php

/*
 * Configuration to JSON converter
 * Written by Job Snijders <job@instituut.net>
 *
 */

use App\Facades\LibrenmsConfig;

$init_modules = ['nodb'];
require __DIR__ . '/includes/init.php';

if (App::runningInConsole()) {
    // fill in db variables for legacy external scripts
    LibrenmsConfig::populateLegacyDbCredentials();

    echo LibrenmsConfig::toJson();
}
