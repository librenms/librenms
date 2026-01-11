<?php

use App\Facades\LibrenmsConfig;

/*
 * LibreNMS per-module discovery performance
 *
 * Copyright (c) 2024 LibreNMS Contributors
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$scale_min = '0';

// Workaround to load the Object from the SQL query array.
// TODO Convert the initial SQL query to Eloquent
$device = DeviceCache::get($device['device_id']);

$attribs = $device->getAttribs();
$modules = LibrenmsConfig::get('discovery_modules');
ksort($modules);

require 'includes/html/graphs/common.inc.php';

foreach ($modules as $module => $module_status) {
    $rrd_filename = Rrd::name($device->hostname, ['discovery-perf', $module]);
    $device_module_status = $attribs['discover_' . $module] ?? null;
    if ($device_module_status || ($module_status && $device_module_status === null) ||
        (LibrenmsConfig::getOsSetting($device->os, 'discovery_modules.' . $module) && $device_module_status === null)) {
        if (Rrd::checkRrdExists($rrd_filename)) {
            $ds['ds'] = 'discovery';
            $ds['descr'] = $module;
            $ds['filename'] = $rrd_filename;
            $rrd_list[] = $ds;
        }
    }
}

$unit_text = 'Seconds';
$simple_rrd = false;
$nototal = true;
$text_orig = true;
$colours = 'manycolours';
require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
