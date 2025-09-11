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

<<<<<<< HEAD
use LibreNMS\Config;
=======
use App\Facades\LibrenmsConfig;
>>>>>>> 8f8bf04ba52459b79a5000bfe1ae9e50c0d7be8e
use LibreNMS\Util\Debug;

$init_modules = ['web', 'auth'];
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (! Auth::check()) {
    exit('Unauthorized');
}

Debug::set(strpos($_SERVER['PATH_INFO'], 'debug'));

$report = basename($vars['report']);
<<<<<<< HEAD
if ($report && file_exists(Config::get('install_dir') . "/includes/html/reports/$report.csv.inc.php")) {
=======
if ($report && file_exists(LibrenmsConfig::get('install_dir') . "/includes/html/reports/$report.csv.inc.php")) {
>>>>>>> 8f8bf04ba52459b79a5000bfe1ae9e50c0d7be8e
    if (! Debug::isEnabled()) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $report . '-' . date('Ymd') . '.csv"');
    }

    $csv = [];
<<<<<<< HEAD
    require Config::get('install_dir') . "/includes/html/reports/$report.csv.inc.php";
=======
    require LibrenmsConfig::get('install_dir') . "/includes/html/reports/$report.csv.inc.php";
>>>>>>> 8f8bf04ba52459b79a5000bfe1ae9e50c0d7be8e
    foreach ($csv as $line) {
        echo implode(',', $line) . "\n";
    }
} else {
    echo "Report not found.\n";
}
