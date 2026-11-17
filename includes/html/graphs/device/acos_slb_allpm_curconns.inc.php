<?php

/*
 * LibreNMS module to display A10 ACOS SLB Service Group Member details
 *
 * Graphs all Service Group Members belonging to the Service Group ($vars['id']).
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$component = new LibreNMS\Component();
$components = $component->getComponents($device['device_id']);
$components = $components[$device['device_id']] ?? [];

include 'includes/html/graphs/common.inc.php';
$graph_params->scale_min = 0;

$rrd_options[] = 'COMMENT:ACOS SLB Pool Members          Now      Avg      Max\\n';
$colours = array_merge(\App\Facades\LibrenmsConfig::get('graph_colours.mixed'), \App\Facades\LibrenmsConfig::get('graph_colours.manycolours'), \App\Facades\LibrenmsConfig::get('graph_colours.manycolours'));
$count = 0;

if (isset($components[$vars['id']]) && $components[$vars['id']]['type'] == 'acos-slb-pool') {
    $parent = $components[$vars['id']]['UID'];

    foreach ($components as $comp) {
        if ($comp['type'] != 'acos-slb-member') {
            continue;
        }
        if (! str_starts_with((string) $comp['UID'], (string) $parent)) {
            continue;
        }

        $label = $comp['label'];
        $hash = $comp['hash'];
        $rrd_filename = Rrd::name($device['hostname'], [$comp['type'], $label, $hash]);
        if (Rrd::checkRrdExists($rrd_filename)) {
            $colour = $colours[$count] ?? $colours[0];

            $rrd_options[] = 'DEF:DS' . $count . '=' . $rrd_filename . ':curconns:AVERAGE';
            $rrd_options[] = 'LINE1.25:DS' . $count . '#' . $colour . ':' . str_pad(substr(str_replace(':', '\:', (string) $label), 0, 40), 40);
            $rrd_options[] = 'GPRINT:DS' . $count . ':LAST:%6.2lf%s';
            $rrd_options[] = 'GPRINT:DS' . $count . ':AVERAGE:%6.2lf%s';
            $rrd_options[] = 'GPRINT:DS' . $count . ":MAX:%6.2lf%s\l";
            $count++;
        }
    }
}
