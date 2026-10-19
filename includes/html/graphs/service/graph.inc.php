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

use App\Facades\LibrenmsConfig;
use App\Models\Service;
use LibreNMS\Data\Graphing\GraphParameters;
use LibreNMS\Services;

/** @var Service $service */
/** @var GraphParameters $graph_params */
$rrd_filename = Rrd::name($service->device->hostname, ['services', $service->service_id]);

$service_ds = json_decode(htmlspecialchars_decode((string) $service->service_ds), true) ?: [];

$check_script = Services::customCheckPath($service->service_type);
if (is_file($check_script)) {
    $serviceData = array_merge([
        'service_param' => '',
        'service_ip' => '',
        'hostname' => '',
        'overwrite_ip' => '',
        'service_type' => $service->service_type,
    ], $service->toArray());
    $service = $serviceData;
    include $check_script;

    if (isset($check_ds)) {
        $service_ds = json_decode($check_ds, true) ?: [];
    }
}

include 'includes/html/graphs/common.inc.php';
$graph_params->scale_min = 0;

$ds = isset($vars['ds'], $service_ds[$vars['ds']]) ? $vars['ds'] : array_key_first($service_ds);

if ($ds) {
    if (isset($check_graph[$ds])) {
        $rrd_options = array_map(trim(...), $check_graph[$ds]);
    } else {
        $rrd_options[] = 'COMMENT:                      Now     Avg      Max\n';

        $tint = preg_match('/loss/i', (string) $ds) ? 'pinks' : 'blues';
        $color_avg = LibrenmsConfig::get("graph_colours.$tint.2");
        $color_max = LibrenmsConfig::get("graph_colours.$tint.0");

        $label = is_array($service_ds[$ds]) ? ($service_ds[$ds]['uom'] ?? '') : ($service_ds[$ds] ?? '');
        $legend = ucfirst((string) $ds) . ($label !== '' ? " ($label)" : '');

        $rrd_options[] = "DEF:DS=$rrd_filename:$ds:AVERAGE";
        $rrd_options[] = "DEF:DS_MAX=$rrd_filename:$ds:MAX";
        $rrd_options[] = "AREA:DS_MAX#$color_max:";
        $rrd_options[] = "AREA:DS#$color_avg:" . str_pad(substr($legend, 0, 15), 15);
        $rrd_options[] = 'GPRINT:DS:LAST:%5.2lf%s';
        $rrd_options[] = 'GPRINT:DS:AVERAGE:%5.2lf%s';
        $rrd_options[] = 'GPRINT:DS_MAX:MAX:%5.2lf%s\l';
    }
}
