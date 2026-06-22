<?php

/*
 * LibreNMS module to graph Juniper RPM SLA RTT spread (min/avg/max/stddev, any probe type)
 *
 * Copyright (c) 2026 Christian Saffer <christian@saffer.in>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$sla = dbFetchRow('SELECT `sla_nr`, `rtt_type` FROM `slas` WHERE `sla_id` = ?', [$vars['id']]);

require 'includes/html/graphs/common.inc.php';
$graph_params->scale_min = 0;
$rrd_filename = Rrd::name($device['hostname'], ['sla', $sla['sla_nr'], $sla['rtt_type']]);
$rrd_base = Rrd::name($device['hostname'], ['sla', $sla['sla_nr']]);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_options[] = 'COMMENT:RTT\:                Cur     Min     Max     Avg\\n';

    $rrd_options[] = 'DEF:Min=' . $rrd_filename . ':MinRttUs:AVERAGE';
    $rrd_options[] = 'DEF:Max=' . $rrd_filename . ':MaxRttUs:AVERAGE';
    $rrd_options[] = 'DEF:StdDev=' . $rrd_filename . ':StdDevRttUs:AVERAGE';
    $rrd_options[] = 'CDEF:Spread=Max,Min,-';

    // Shade the min-to-max band
    $rrd_options[] = 'AREA:Min#FFFFFF00';
    $rrd_options[] = 'AREA:Spread#3399ff40::STACK';

    $rrd_options[] = 'LINE1:Min#3399ff:Minimum (ms)     ';
    $rrd_options[] = 'GPRINT:Min:LAST:%6.2lf';
    $rrd_options[] = 'GPRINT:Min:MIN:%6.2lf';
    $rrd_options[] = 'GPRINT:Min:MAX:%6.2lf';
    $rrd_options[] = 'GPRINT:Min:AVERAGE:%6.2lf\\l';

    $rrd_options[] = 'LINE1:Max#3399ff:Maximum (ms)     ';
    $rrd_options[] = 'GPRINT:Max:LAST:%6.2lf';
    $rrd_options[] = 'GPRINT:Max:MIN:%6.2lf';
    $rrd_options[] = 'GPRINT:Max:MAX:%6.2lf';
    $rrd_options[] = 'GPRINT:Max:AVERAGE:%6.2lf\\l';

    if (Rrd::checkRrdExists($rrd_base)) {
        $rrd_options[] = 'DEF:Avg=' . $rrd_base . ':rtt:AVERAGE';
        $rrd_options[] = 'LINE2:Avg#0000ee:Average (ms)     ';
        $rrd_options[] = 'GPRINT:Avg:LAST:%6.2lf';
        $rrd_options[] = 'GPRINT:Avg:MIN:%6.2lf';
        $rrd_options[] = 'GPRINT:Avg:MAX:%6.2lf';
        $rrd_options[] = 'GPRINT:Avg:AVERAGE:%6.2lf\\l';
    }

    $rrd_options[] = 'LINE1:StdDev#ff9900:Std deviation (ms)';
    $rrd_options[] = 'GPRINT:StdDev:LAST:%6.2lf';
    $rrd_options[] = 'GPRINT:StdDev:MIN:%6.2lf';
    $rrd_options[] = 'GPRINT:StdDev:MAX:%6.2lf';
    $rrd_options[] = 'GPRINT:StdDev:AVERAGE:%6.2lf\\l';
}
