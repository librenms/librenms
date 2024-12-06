<?php
/*
 * LibreNMS module to display Cisco Class-Based QoS Details
 *
 * Copyright (c) 2015 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

include 'includes/html/graphs/common.inc.php';
$rrd_options .= ' -l 0 -E ';
$rrd_options .= " COMMENT:'Class-Map              Now      Avg      Max\\n'";
$rrd_additions = '';

$colours = array_merge(\LibreNMS\Config::get('graph_colours.mixed'), \LibreNMS\Config::get('graph_colours.manycolours'), \LibreNMS\Config::get('graph_colours.manycolours'));

$qos = \App\Models\Qos::find($vars['qos_id']);

if ($qos->type == 'cisco_cbqos_classmap') {
    // We only want to show our stats
    $graphs = collect([$qos]);
} else {
    // We want to show stats for all children
    $graphs = \App\Models\Qos::where('parent_id', $qos->qos_id)->orderBy('title')->get();
}

// Stack the area on the second and subsequent DS's
$stack = '';
$count = 0;

foreach ($graphs as $thisQos) {
    $rrd_filename = Rrd::name($device['hostname'], $thisQos->rrd_id);

    if ($count != 0) {
        $stack = ':STACK ';
    }

    // Grab a colour from the array.
    if (isset($colours[$count])) {
        $colour = $colours[$count];
    } else {
        d_echo("\nError: Out of colours. Have: " . (count($colours) - 1) . ', Requesting:' . $count);
    }

    $rrd_additions .= ' DEF:DS' . $count . '=' . $rrd_filename . ':' . $cbqos_parameter_name . ':AVERAGE ';
    $rrd_additions .= ' CDEF:MOD' . $count . '=DS' . $count . ',' . $cbqos_operator_param . ',' . $cbqos_operator . ' ';
    $rrd_additions .= ' AREA:MOD' . $count . '#' . $colour . ":'" . str_pad(substr($thisQos->title, 0, 15), 15) . "'" . $stack;
    $rrd_additions .= ' GPRINT:MOD' . $count . ':LAST:%6.2lf%s ';
    $rrd_additions .= ' GPRINT:MOD' . $count . ':AVERAGE:%6.2lf%s ';
    $rrd_additions .= ' GPRINT:MOD' . $count . ":MAX:%6.2lf%s\l ";

    $count++;
}

if ($rrd_additions == '') {
    // We didn't add any data sources.
    d_echo('<pre>No DS to add</pre>');
} else {
    $rrd_options .= $rrd_additions;
}
