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
$options = [];
$options['filter']['type'] = ['=', 'Cisco-CBQOS'];
$components = $component->getComponents($device['device_id'], $options);

// We only care about our device id.
$components = $components[$device['device_id']];

// Determine a policy to show.
if (! isset($vars['policy'])) {
    foreach ($components as $id => $array) {
        if (($array['qos-type'] == 1) && ($array['ifindex'] == $port['ifIndex']) && ($array['parent'] == 0)) {
            // Found the first policy
            $vars['policy'] = $id;
            continue;
        }
    }
}

include 'includes/html/graphs/common.inc.php';
$rrd_options .= ' -l 0 -E ';
$rrd_options .= " COMMENT:'Class-Map              Now      Avg      Max\\n'";
$rrd_additions = '';

$colours = array_merge(\LibreNMS\Config::get('graph_colours.mixed'), \LibreNMS\Config::get('graph_colours.manycolours'), \LibreNMS\Config::get('graph_colours.manycolours'));
$count = 0;

d_echo('<pre>Policy: ' . $vars['policy']);
d_echo("\nSP-OBJ: " . $components[$vars['policy']]['sp-obj']);
foreach ($components as $id => $array) {
    $addtograph = false;

    // We only care about children of the selected policy.
    if (($array['qos-type'] == 2) && ($array['parent'] == $components[$vars['policy']]['sp-obj']) && ($array['sp-id'] == $components[$vars['policy']]['sp-id'])) {
        // Are we trying to only graph a single class?
        if (isset($vars['class'])) {
            // Yes, is this the selected class
            if ($vars['class'] == $id) {
                $addtograph = true;
            }
        } else {
            // No, Graph everything
            $addtograph = true;
        }

        // Add the class map to the graph
        if ($addtograph === true) {
            d_echo("\n  Class: " . $components[$id]['label'] . "\t+ added to the graph");
            $rrd_filename = Rrd::name($device['hostname'], ['port', $array['ifindex'], 'cbqos', $array['sp-id'], $array['sp-obj']]);

            if (Rrd::checkRrdExists($rrd_filename)) {
                // Stack the area on the second and subsequent DS's
                $stack = '';
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
                $rrd_additions .= ' AREA:MOD' . $count . '#' . $colour . ":'" . str_pad(substr($components[$id]['label'], 0, 15), 15) . "'" . $stack;
                $rrd_additions .= ' GPRINT:MOD' . $count . ':LAST:%6.2lf%s ';
                $rrd_additions .= ' GPRINT:MOD' . $count . ':AVERAGE:%6.2lf%s ';
                $rrd_additions .= ' GPRINT:MOD' . $count . ":MAX:%6.2lf%s\\\l ";

                $count++;
            } // End if file exists
        } else {
            d_echo("\n  Class: " . $components[$id]['label'] . "\t- NOT added to the graph");
        } // End if addtograph
    }
}
d_echo('</pre>');

if ($rrd_additions == '') {
    // We didn't add any data sources.
    d_echo('<pre>No DS to add</pre>');
} else {
    $rrd_options .= $rrd_additions;
}
