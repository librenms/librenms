<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (strpos($_SERVER['PATH_INFO'], 'debug')) {
    $debug = '1';
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_reporting', E_ALL);
} else {
    $debug = false;
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 0);
    ini_set('error_reporting', 0);
}

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

$report = mres($vars['report']);
if (!empty($report) && file_exists("includes/reports/$report.csv.inc.php")) {
    if ($debug === false) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$report.'-'.date('Ymd').'.csv"');
    }

    $csv = array();
    require $config['install_dir'] . "/html/includes/reports/$report.csv.inc.php";
    foreach ($csv as $line) {
        echo implode(',', $line)."\n";
    }
} else {
    echo "Report not found.\n";
}
