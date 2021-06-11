<?php
/*
 * LibreNMS module to display captured NTP statistics
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
$options = [];
$options['filter']['type'] = ['=', 'ntp'];
$components = $component->getComponents($device['device_id'], $options);

// We only care about our device id.
$components = $components[$device['device_id']];

include 'includes/html/graphs/common.inc.php';
$rrd_options .= ' -l 0 -E ';
$rrd_options .= " --vertical-label='Seconds'";
$rrd_options .= " COMMENT:'Dispersion (s)         Now      Min      Max\\n'";
$rrd_additions = '';

$count = 0;
foreach ($components as $id => $array) {
    $rrd_filename = Rrd::name($device['hostname'], ['ntp', $array['peer']]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        // Grab a color from the array.
        $color = \LibreNMS\Config::get("graph_colours.mixed.$count", \LibreNMS\Config::get('graph_colours.oranges.' . ($count - 7)));

        $rrd_additions .= ' DEF:DS' . $count . '=' . $rrd_filename . ':dispersion:AVERAGE ';
        $rrd_additions .= ' LINE1.25:DS' . $count . '#' . $color . ":'" . str_pad(substr($array['peer'], 0, 15), 15) . "'" . $stack;
        $rrd_additions .= ' GPRINT:DS' . $count . ':LAST:%7.2lf ';
        $rrd_additions .= ' GPRINT:DS' . $count . ':MIN:%7.2lf ';
        $rrd_additions .= ' GPRINT:DS' . $count . ':MAX:%7.2lf\\l ';
        $count++;
    }
}

if ($rrd_additions == '') {
    // We didn't add any data points.
} else {
    $rrd_options .= $rrd_additions;
}
