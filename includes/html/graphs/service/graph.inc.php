<?php
/*
 * LibreNMS module to display graphing for Nagios Service
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// variables from previous scripts
/** @var \App\Models\Service $service */
/** @var string $rrd_filename */
/** @var string $rrd_options */
/** @var array $vars */
$service_check = \LibreNMS\Services::makeCheck($service);

include 'includes/html/graphs/common.inc.php';
$rrd_options .= ' -l 0 -E ';
$rrd_options .= " COMMENT:'                      Now     Avg      Max\\n'";

$check_ds = $service_check->serviceDataSets();
if (! empty($check_ds)) {
    $ds = isset($check_ds[$vars['ds']]) ? $vars['ds'] : \Illuminate\Support\Arr::first($check_ds);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_options .= $service_check->graphRrdCommands($rrd_filename, $ds);

        return;
    }
}

graph_error('No Graphs');
