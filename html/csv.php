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

use LibreNMS\Config;
use LibreNMS\Util\Debug;

$init_modules = ['web', 'auth'];
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (! Auth::check()) {
    exit('Unauthorized');
}

Debug::set(strpos($_SERVER['PATH_INFO'], 'debug'));

$report = basename($vars['report']);
if ($report && file_exists(Config::get('install_dir') . "/includes/html/reports/$report.csv.inc.php")) {
    if (! Debug::isEnabled()) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $report . '-' . date('Ymd') . '.csv"');
    }

    $csv = [];
    require Config::get('install_dir') . "/includes/html/reports/$report.csv.inc.php";
    foreach ($csv as $line) {
        echo implode(',', $line) . "\n";
    }
} else {
    echo "Report not found.\n";
}
