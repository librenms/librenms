<?php
/*
 * LibreNMS module to capture statistics from the CISCO-NTP-MIB
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

require_once "../includes/component.php";
$component = new component();
$options = array();
$options['filter']['type'] = array('=','Cisco-NTP');
$components = $component->getComponents($device['device_id'],$options);

// We only care about our device id.
$components = $components[$device['device_id']];

include "includes/graphs/common.inc.php";
$rrd_options .= " -l 0 -E ";
$rrd_options .= " COMMENT:'Stratum             Now  Min  Max\\n'";
$rrd_additions = "";

$count = 0;
foreach ($components as $id => $array) {
    $rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename("ntp-".$array['peer'].".rrd");

    if (file_exists($rrd_filename)) {
        // Grab a color from the array.
        if ( isset($config['graph_colours']['mixed'][$count]) ) {
            $color = $config['graph_colours']['mixed'][$count];
        } else {
            $color = $config['graph_colours']['oranges'][$count-7];
        }

        $rrd_additions .= " DEF:DS" . $count . "=" . $rrd_filename . ":stratum:AVERAGE ";
        $rrd_additions .= " LINE1.25:DS" . $count . "#" . $color . ":'" . str_pad(substr($array['peer'],0,15),15) . "'" . $stack;
        $rrd_additions .= " GPRINT:DS" . $count . ":LAST:%2.0lf ";
        $rrd_additions .= " GPRINT:DS" . $count .    ":MIN:%2.0lf ";
        $rrd_additions .= " GPRINT:DS" . $count . ":MAX:%2.0lf\\\l ";
        $count++;
    }
}

if ($rrd_additions == "") {
    // We didn't add any data points.
} else {
    $rrd_options .= $rrd_additions;
}
