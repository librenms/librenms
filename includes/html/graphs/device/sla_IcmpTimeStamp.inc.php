<?php
/*
 * LibreNMS module to Graph Juniper RPM IcmpTimeStamp metrics
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$sla = dbFetchRow('SELECT `sla_nr` FROM `slas` WHERE `sla_id` = ?', [$vars['id']]);

require 'includes/html/graphs/common.inc.php';
$rrd_options .= ' -l 0 -E ';
$rrd_filename = Rrd::name($device['hostname'], ['sla', $sla['sla_nr'], 'IcmpTimeStamp']);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_options .= " COMMENT:'Packet loss\:        Cur      Avg     Min     Max\\n' ";

    // Calculating percentage
    $rrd_options .= ' DEF:ProbeResponses=' . $rrd_filename . ':ProbeResponses:AVERAGE ';
    $rrd_options .= ' DEF:ProbeLoss=' . $rrd_filename . ':ProbeLoss:AVERAGE ';
    $rrd_options .= ' CDEF:ProbeCount=ProbeResponses,ProbeLoss,+ ';
    $rrd_options .= ' CDEF:PercentageLoss=ProbeLoss,UNKN,NE,0,ProbeLoss,IF,ProbeCount,/,100,*,CEIL ';

    $rrd_options .= " LINE1:PercentageLoss#CC0000:'PercentageLoss'";
    $rrd_options .= ' GPRINT:PercentageLoss:LAST:%6.1lf%% ';
    $rrd_options .= ' GPRINT:PercentageLoss:AVERAGE:%6.1lf%% ';
    $rrd_options .= ' GPRINT:PercentageLoss:MIN:%6.1lf%% ';
    $rrd_options .= ' GPRINT:PercentageLoss:MAX:%6.1lf%%\\l ';
}
