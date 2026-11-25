<?php

/*
 * LibreNMS module to display a single A10 ACOS SLB Virtual Server's
 * Current Connections - scoped to one VS component (via $vars['id']),
 * not summed/overlaid with every other Virtual Server on the device.
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$component = new LibreNMS\Component();
$options = ['filter' => ['id' => ['=', $vars['id'] ?? 0]]];
$components = $component->getComponents($device['device_id'], $options);
$components = $components[$device['device_id']] ?? [];

$comp = $components[$vars['id']] ?? null;
if ($comp === null || $comp['type'] != 'acos-slb-vs') {
    throw new \LibreNMS\Exceptions\RrdGraphException('Virtual Server not found');
}

$rrd_filename = Rrd::name($device['hostname'], [$comp['type'], $comp['label'], $comp['hash']]);
if (! Rrd::checkRrdExists($rrd_filename)) {
    throw new \LibreNMS\Exceptions\RrdGraphException('No Data');
}

include 'includes/html/graphs/common.inc.php';
$graph_params->scale_min = 0;

$rrd_options[] = 'COMMENT:' . str_pad(substr(str_replace(':', '\:', (string) $comp['label']), 0, 40), 40) . '   Now      Avg      Max\n';
$rrd_options[] = 'DEF:curconns=' . $rrd_filename . ':curconns:AVERAGE';
$rrd_options[] = 'AREA:curconns#1FA8DC:Current Connections';
$rrd_options[] = 'GPRINT:curconns:LAST:%6.2lf%s';
$rrd_options[] = 'GPRINT:curconns:AVERAGE:%6.2lf%s';
$rrd_options[] = "GPRINT:curconns:MAX:%6.2lf%s\l";
