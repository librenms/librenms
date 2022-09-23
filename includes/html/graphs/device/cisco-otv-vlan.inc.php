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

$component = new LibreNMS\Component();
$options['filter']['type'] = ['=', 'Cisco-OTV'];
$components = $component->getComponents($device['device_id'], $options);

// We only care about our device id.
$components = $components[$device['device_id']];

include 'includes/html/graphs/common.inc.php';
$rrd_options .= ' -l 0 -E ';
$rrd_options .= " COMMENT:'VLANs               Now     Min    Max\\n'";
$rrd_additions = '';

$count = 0;
foreach ($components as $id => $array) {
    if ($array['otvtype'] == 'overlay') {
        $rrd_filename = Rrd::name($device['hostname'], ['cisco', 'otv', $array['label'], 'vlan']);

        if (Rrd::checkRrdExists($rrd_filename)) {
            // Stack the area on the second and subsequent DS's
            $stack = '';
            if ($count != 0) {
                $stack = ':STACK ';
            }

            // Grab a color from the array.
            $color = \LibreNMS\Config::get("graph_colours.mixed.$count", \LibreNMS\Config::get('graph_colours.oranges.' . ($count - 7)));

            $rrd_additions .= ' DEF:DS' . $count . '=' . $rrd_filename . ':count:AVERAGE ';
            $rrd_additions .= ' AREA:DS' . $count . '#' . $color . ":'" . str_pad(substr($components[$id]['label'], 0, 15), 15) . "'" . $stack;
            $rrd_additions .= ' GPRINT:DS' . $count . ':LAST:%4.0lf%s ';
            $rrd_additions .= ' GPRINT:DS' . $count . ':MIN:%4.0lf%s ';
            $rrd_additions .= ' GPRINT:DS' . $count . ":MAX:%4.0lf%s\\\l ";
            $count++;
        }
    }
}

if ($rrd_additions == '') {
    // We didn't add any data points.
} else {
    $rrd_options .= $rrd_additions;
}
