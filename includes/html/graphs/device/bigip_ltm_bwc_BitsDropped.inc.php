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

// Is the ID we are looking for a valid LTM VS
if (isset($components[$vars['id']])) {
    $label = $components[$vars['id']]['label'];
    $hash = $components[$vars['id']]['hash'];

    include 'includes/html/graphs/common.inc.php';
    $rrd_options .= ' -l 0 -E ';
    $rrd_options .= " COMMENT:'Bits           Now      Ave      Max\\n'";

    $rrd_filename = Rrd::name($device['hostname'], ['f5-ltm-bwc', $label, $hash]);
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_options .= ' DEF:INBYTES=' . $rrd_filename . ':bytesdropped:AVERAGE ';
        $rrd_options .= ' CDEF:INBITS=INBYTES,8,* ';
        $rrd_options .= " LINE1.25:INBITS#CC0000:'Traffic Dropped '";
        $rrd_options .= ' GPRINT:INBITS:LAST:%6.2lf%s ';
        $rrd_options .= ' GPRINT:INBITS:AVERAGE:%6.2lf%s ';
        $rrd_options .= " GPRINT:INBITS:MAX:%6.2lf%s\l ";
    }
}
