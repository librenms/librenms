<?php
/*
 * LibreNMS module to display F5 GTM Wide IP Details
 *
 * Adapted from F5 LTM module by Darren Napper
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
$options['filter']['type'] = ['=', 'f5-gtm-wide'];
$components = $component->getComponents($device['device_id'], $options);

// We only care about our device id.
$components = $components[$device['device_id']];

// Is the ID we are looking for a valid GTM Pool
if (isset($components[$vars['id']])) {
    $label = $components[$vars['id']]['label'];
    $hash = $components[$vars['id']]['hash'];

    $rrd_filename = Rrd::name($device['hostname'], ['f5-gtm-wide', $label, $hash]);
    if (Rrd::checkRrdExists($rrd_filename)) {
        require 'includes/html/graphs/common.inc.php';
        $ds = 'dropped';

        $colour_area = '9999cc';
        $colour_line = '0000cc';

        $colour_area_max = '9999cc';

        $graph_max = 1;
        $graph_min = 0;

        $unit_text = 'Dropped Requests';
        $line_text = 'Dropped Requests';
        require 'includes/html/graphs/generic_simplex.inc.php';
    }
}
