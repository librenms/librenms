<?php
/*
 * LibreNMS module to display F5 LTM Bandwidth Controller Details
 *
 * Copyright (c) 2019 Yacine BENAMSILI <https://github.com/yac01/ yacine.benamsili@homail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$component = new LibreNMS\Component();
$options = [];
$options['filter']['type'] = ['=', 'f5-ltm-bwc'];
$components = $component->getComponents($device['device_id'], $options);

// We only care about our device id.
$components = $components[$device['device_id']];

include 'includes/html/graphs/common.inc.php';
$rrd_options .= ' -l 0 -E ';
$rrd_options .= " COMMENT:'LTM Bandwidth Controller       Now      Avg      Max\\n'";
$colours = array_merge(\LibreNMS\Config::get('graph_colours.mixed'), \LibreNMS\Config::get('graph_colours.manycolours'));
$colcount = 0;
$count = 0;

// add all LTM VS on this device.
foreach ($components as $compid => $comp) {
    $label = $comp['label'];
    $hash = $comp['hash'];
    $rrd_filename = Rrd::name($device['hostname'], [$comp['type'], $label, $hash]);
    if (Rrd::checkRrdExists($rrd_filename)) {
        // Grab a colour from the array.
        if (isset($colours[$colcount])) {
            $colour = $colours[$colcount];
        } else {
            $colcount = 0;
            $colour = $colours[$colcount];
        }

        $rrd_options .= ' DEF:DS' . $count . '=' . $rrd_filename . ':bytesin:AVERAGE ';
        $rrd_options .= ' CDEF:MOD' . $count . '=DS' . $count . ',8,* ';
        $rrd_options .= ' LINE1.25:MOD' . $count . '#' . $colour . ":'" . str_pad(substr($label, 0, 60), 60) . "'";
        $rrd_options .= ' GPRINT:MOD' . $count . ':LAST:%6.2lf%s ';
        $rrd_options .= ' GPRINT:MOD' . $count . ':AVERAGE:%6.2lf%s ';
        $rrd_options .= ' GPRINT:MOD' . $count . ":MAX:%6.2lf%s\l ";
        $count++;
        $colcount++;
    }
}
