<?php

/*
 * LibreNMS module to display A10 ACOS SLB Virtual Server details
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// Only the top-level VIP (acos-slb-vs already aggregates all of its ports,
// so including acos-slb-vport here too would double-count traffic).
//
// With ~1000s of Virtual Servers, one line per VS is unreadable and burns
// through the colour palette many times over, so this sums every VS's
// curconns into a single total line instead of plotting each separately.
$component = new LibreNMS\Component();
$options = [];
$options['filter']['type'] = ['=', 'acos-slb-vs'];
$components = $component->getComponents($device['device_id'], $options);
$components = $components[$device['device_id']] ?? [];

include 'includes/html/graphs/common.inc.php';
$graph_params->scale_min = 0;

$rrd_options[] = 'COMMENT:ACOS SLB Virtual Servers - Total Current Connections\\n';

$defs = [];
$i = 0;
foreach ($components as $comp) {
    $label = $comp['label'];
    $hash = $comp['hash'];
    $rrd_filename = Rrd::name($device['hostname'], [$comp['type'], $label, $hash]);
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_options[] = 'DEF:DS' . $i . '=' . $rrd_filename . ':curconns:AVERAGE';
        $defs[] = 'DS' . $i;
        $i++;
    }
}

if (! empty($defs)) {
    $cdef = 'CDEF:total=' . $defs[0];
    for ($j = 1; $j < count($defs); $j++) {
        $cdef .= ',' . $defs[$j] . ',ADDNAN';
    }
    $rrd_options[] = $cdef;
    $rrd_options[] = 'AREA:total#1FA8DC:Total Current Connections';
    $rrd_options[] = 'GPRINT:total:LAST:Current\: %6.2lf%s';
    $rrd_options[] = 'GPRINT:total:AVERAGE:Average\: %6.2lf%s';
    $rrd_options[] = 'GPRINT:total:MAX:Maximum\: %6.2lf%s\l';
}
