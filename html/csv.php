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

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

set_debug(strpos($_SERVER['PATH_INFO'], 'debug'));

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
