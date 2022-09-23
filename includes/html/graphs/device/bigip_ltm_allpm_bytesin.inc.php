<?php
/*
 * LibreNMS module to display F5 LTM Virtual Server Details
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$component = new LibreNMS\Component();
$components = $component->getComponents($device['device_id']);

// We only care about our device id.
$components = $components[$device['device_id']];

// We extracted all the components for this device, now lets only get the LTM ones.
$keep = [];
$types = ['f5-ltm-pool', 'f5-ltm-poolmember'];
foreach ($components as $k => $v) {
    foreach ($types as $type) {
        if ($v['type'] == $type) {
            $keep[$k] = $v;
        }
    }
}
$components = $keep;

include 'includes/html/graphs/common.inc.php';
$rrd_options .= ' -l 0 -E ';
$rrd_options .= " COMMENT:'LTM Pool Members                               Now      Avg      Max\\n'";
$colours = array_merge(\LibreNMS\Config::get('graph_colours.mixed'), \LibreNMS\Config::get('graph_colours.manycolours'), \LibreNMS\Config::get('graph_colours.manycolours'));
$count = 0;
d_echo('<pre>');

// Is the ID we are looking for a valid LTM VS Pool
if ($components[$vars['id']]['type'] == 'f5-ltm-pool') {
    $parent = $components[$vars['id']]['UID'];

    // Find all pool members
    foreach ($components as $compid => $comp) {
        if ($comp['type'] != 'f5-ltm-poolmember') {
            continue;
        }
        if (! strstr($comp['UID'], $parent)) {
            continue;
        }

        $label = $comp['label'];
        $hash = $comp['hash'];
        $rrd_filename = Rrd::name($device['hostname'], [$comp['type'], $label, $hash]);
        if (Rrd::checkRrdExists($rrd_filename)) {
            d_echo("\n  Adding PM: " . $label . "\t+ added to the graph");

            // Grab a colour from the array.
            if (isset($colours[$count])) {
                $colour = $colours[$count];
            } else {
                d_echo("\nError: Out of colours. Have: " . (count($colours) - 1) . ', Requesting:' . $count);
            }

            $rrd_options .= ' DEF:DS' . $count . '=' . $rrd_filename . ':bytesin:AVERAGE ';
            $rrd_options .= ' CDEF:MOD' . $count . '=DS' . $count . ',8,* ';
            $rrd_options .= ' LINE1.25:MOD' . $count . '#' . $colour . ":'" . str_pad(substr($label, 0, 40), 40) . "'";
            $rrd_options .= ' GPRINT:MOD' . $count . ':LAST:%6.2lf%s ';
            $rrd_options .= ' GPRINT:MOD' . $count . ':AVERAGE:%6.2lf%s ';
            $rrd_options .= ' GPRINT:MOD' . $count . ":MAX:%6.2lf%s\l ";
            $count++;
        }
    } // End Foreach
}
d_echo('</pre>');
