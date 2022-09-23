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
$options = [];
$options['filter']['type'] = ['=', 'f5-ltm-vs'];
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

    $rrd_filename = Rrd::name($device['hostname'], ['f5-ltm-vs', $label, $hash]);
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_options .= ' DEF:INBYTES=' . $rrd_filename . ':bytesin:AVERAGE ';
        $rrd_options .= ' CDEF:INBITS=INBYTES,8,* ';
        $rrd_options .= " LINE1.25:INBITS#330033:'Bits In '";
        $rrd_options .= ' GPRINT:INBITS:LAST:%6.2lf%s ';
        $rrd_options .= ' GPRINT:INBITS:AVERAGE:%6.2lf%s ';
        $rrd_options .= " GPRINT:INBITS:MAX:%6.2lf%s\l ";

        $rrd_options .= ' DEF:OUTBYTES=' . $rrd_filename . ':bytesout:AVERAGE ';
        $rrd_options .= ' CDEF:OUTBITS=OUTBYTES,8,* ';
        $rrd_options .= " LINE1.25:OUTBITS#FF6600:'Bits Out'";
        $rrd_options .= ' GPRINT:OUTBITS:LAST:%6.2lf%s ';
        $rrd_options .= ' GPRINT:OUTBITS:AVERAGE:%6.2lf%s ';
        $rrd_options .= " GPRINT:OUTBITS:MAX:%6.2lf%s\l ";
    }
}
