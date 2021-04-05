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

// Get a list of all services for this device.
require_once 'includes/services.inc.php';
$services = service_get($device['device_id']);

// Determine which key is the service we want to show.
if (isset($vars['id'])) {
    // Service is set, find its key.
    foreach ($services as $key => $service) {
        if ($service['service_id'] == $vars['id']) {
            // We have found the service we want.
            $vars['service'] = $key;
        }
    }
} else {
    // No service set, set the first one.
    if (isset($services[0])) {
        $vars['service'] = 0;
    }
}

// We know our service. build the filename.
$rrd_filename = Rrd::name($device['hostname'], ['services', $services[$vars['service']]['service_id']]);

// if we have a script for this check, use it.
$check_script = \LibreNMS\Config::get('install_dir') . '/includes/services/check_' . strtolower($services[$vars['service']]['service_type']) . '.inc.php';
if (is_file($check_script)) {
    include $check_script;

    // If we have a replacement DS use it.
    if (isset($check_ds)) {
        $services[$vars['service']]['service_ds'] = $check_ds;
    }
}

include 'includes/html/graphs/common.inc.php';
$rrd_options .= ' -l 0 -E ';
$rrd_options .= " COMMENT:'                      Now     Avg      Max\\n'";
$rrd_additions = '';

// Remove encoded characters
$services[$vars['service']]['service_ds'] = htmlspecialchars_decode($services[$vars['service']]['service_ds']);

if ($services[$vars['service']]['service_ds'] != '') {
    $graphinfo = json_decode($services[$vars['service']]['service_ds'], true);

    // Do we have a DS set
    if (! isset($graphinfo[$vars['ds']])) {
        foreach ($graphinfo as $k => $v) {
            // Select a DS to display.
            $vars['ds'] = $k;
        }
    }

    // Need: DS name, Label
    $ds = $vars['ds'];
    $label = $graphinfo[$vars['ds']];

    if (Rrd::checkRrdExists($rrd_filename)) {
        if (isset($check_graph)) {
            // We have a graph definition, use it.
            $rrd_additions .= $check_graph[$ds];
        } else {
            // Build the graph ourselves
            if (preg_match('/loss/i', $ds)) {
                $tint = 'pinks';
            } else {
                $tint = 'blues';
            }
            $color_avg = \LibreNMS\Config::get("graph_colours.$tint.2");
            $color_max = \LibreNMS\Config::get("graph_colours.$tint.0");

            $rrd_additions .= ' DEF:DS=' . $rrd_filename . ':' . $ds . ':AVERAGE ';
            $rrd_additions .= ' DEF:DS_MAX=' . $rrd_filename . ':' . $ds . ':MAX ';
            $rrd_additions .= ' AREA:DS_MAX#' . $color_max . ':';
            $rrd_additions .= ' AREA:DS#' . $color_avg . ":'" . str_pad(substr(ucfirst($ds) . ' (' . $label . ')', 0, 15), 15) . "' ";
            $rrd_additions .= ' GPRINT:DS:LAST:%5.2lf%s ';
            $rrd_additions .= ' GPRINT:DS:AVERAGE:%5.2lf%s ';
            $rrd_additions .= ' GPRINT:DS_MAX:MAX:%5.2lf%s\\l ';
        }
    }
}

if ($rrd_additions == '') {
    // We didn't add any data points.
} else {
    $rrd_options .= $rrd_additions;
}
