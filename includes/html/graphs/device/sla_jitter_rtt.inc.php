<?php

/*
 * LibreNMS module to graph Juniper RPM SLA round-trip jitter (any probe type)
 *
 * Copyright (c) 2026 Christian Saffer <christian@saffer.in>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$sla = dbFetchRow('SELECT `sla_nr` FROM `slas` WHERE `sla_id` = ?', [$vars['id']]);

require 'includes/html/graphs/common.inc.php';
$graph_params->scale_min = 0;
$rrd_filename = Rrd::name($device['hostname'], ['sla', $sla['sla_nr'], 'jitter']);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_options[] = 'COMMENT:Jitter\:              Cur     Min     Max     Avg\\n';

    $rrd_options[] = 'DEF:JitterRtt=' . $rrd_filename . ':JitterRtt:AVERAGE';
    $rrd_options[] = 'LINE1.25:JitterRtt#0000ee:Round trip (ms)   ';
    $rrd_options[] = 'GPRINT:JitterRtt:LAST:%6.2lf';
    $rrd_options[] = 'GPRINT:JitterRtt:MIN:%6.2lf';
    $rrd_options[] = 'GPRINT:JitterRtt:MAX:%6.2lf';
    $rrd_options[] = 'GPRINT:JitterRtt:AVERAGE:%6.2lf\\l';

    $rrd_options[] = 'DEF:PeakToPeakJitterRtt=' . $rrd_filename . ':PeakToPeakJitterRtt:AVERAGE';
    $rrd_options[] = 'LINE1.25:PeakToPeakJitterRtt#008C00:Peak to peak (ms) ';
    $rrd_options[] = 'GPRINT:PeakToPeakJitterRtt:LAST:%6.2lf';
    $rrd_options[] = 'GPRINT:PeakToPeakJitterRtt:MIN:%6.2lf';
    $rrd_options[] = 'GPRINT:PeakToPeakJitterRtt:MAX:%6.2lf';
    $rrd_options[] = 'GPRINT:PeakToPeakJitterRtt:AVERAGE:%6.2lf\\l';
}
