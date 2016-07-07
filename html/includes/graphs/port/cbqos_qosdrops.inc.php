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

require_once "../includes/component.php";
$component = new component();
$options['filter']['type'] = array('=','Cisco-CBQOS');
$components = $component->getComponents($device['device_id'],$options);

// We only care about our device id.
$components = $components[$device['device_id']];

// Determine a policy to show.
if (!isset($vars['policy'])) {
    foreach ($components as $id => $array) {
        if ( ($array['qos-type'] == 1) && ($array['ifindex'] == $port['ifIndex'])  && ($array['parent'] == 0) ) {
            // Found the first policy
            $vars['policy'] = $id;
            continue;
        }
    }
}

include "includes/graphs/common.inc.php";
$rrd_options .= " -l 0 -E ";
$rrd_options .= " COMMENT:'Class-Map              Now      Avg      Max\\n'";
$rrd_additions = "";

$count = 0;
foreach ($components as $id => $array) {
    if ( ($array['qos-type'] == 2) && ($array['parent'] == $components[$vars['policy']]['sp-obj']) && ($array['sp-id'] == $components[$vars['policy']]['sp-id'])) {
        $rrd_filename = rrd_name($device['hostname'], array('port', $array['ifindex'], 'cbqos', $array['sp-id'], $array['sp-obj']));

        if (file_exists($rrd_filename)) {
            // Stack the area on the second and subsequent DS's
            $stack = "";
            if ($count != 0) {
                $stack = ":STACK ";
            }

            // Grab a color from the array.
            if ( isset($config['graph_colours']['mixed'][$count]) ) {
                $color = $config['graph_colours']['mixed'][$count];
            }
            else {
                $color = $config['graph_colours']['oranges'][$count-7];
            }

            $rrd_additions .= " DEF:DS" . $count . "=" . $rrd_filename . ":qosdrops:AVERAGE ";
            $rrd_additions .= " CDEF:MOD" . $count . "=DS" . $count . ",8,* ";
            $rrd_additions .= " AREA:MOD" . $count . "#" . $color . ":'" . str_pad(substr($components[$id]['label'],0,15),15) . "'" . $stack;
            $rrd_additions .= " GPRINT:MOD" . $count . ":LAST:%6.2lf%s ";
            $rrd_additions .= " GPRINT:MOD" . $count . ":AVERAGE:%6.2lf%s ";
            $rrd_additions .= " GPRINT:MOD" . $count . ":MAX:%6.2lf%s\\\l ";

            $count++;
        }
    }
}

if ($rrd_additions == "") {
    // We didn't add any data points.
}
else {
    $rrd_options .= $rrd_additions;
}
