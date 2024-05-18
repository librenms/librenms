<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

require 'includes/html/graphs/common.inc.php';

$graph_params->scale_min = 0;

if (\LibreNMS\Config::get('applied_site_style') != 'dark') {
    // light
    $line_color = '#36393d';
    $jitter_color = '#ccd2decc';
} else {
    // dark
    $line_color = '#d1d9eb';
    $jitter_color = '#393d45cc';
}

$rrd_filename = Rrd::name($device['hostname'], 'icmp-perf');

$rrd_options .= ' -X 0 --left-axis-format \'%4.0lfms\' --vertical-label Latency --right-axis 1:0 --right-axis-label \'Loss %\'';

$rrd_options .= ' DEF:ping=' . $rrd_filename . ':avg:AVERAGE';
$rrd_options .= ' DEF:min=' . $rrd_filename . ':min:MIN';
$rrd_options .= ' DEF:max=' . $rrd_filename . ':max:MAX';
$rrd_options .= ' DEF:xmt=' . $rrd_filename . ':xmt:AVERAGE';
$rrd_options .= ' DEF:rcv=' . $rrd_filename . ':rcv:AVERAGE';
$rrd_options .= ' CDEF:top=max,min,-';
$rrd_options .= ' CDEF:loss=xmt,rcv,-,xmt,/,100,*';

// Legend Header
$rrd_options .= " 'COMMENT:Milliseconds      Cur      Min     Max     Avg\\n'";

// Min/Max area invisible min line with max (-min) area stacked on top
$rrd_options .= ' LINE:min#00000000:';
$rrd_options .= " AREA:top$jitter_color::STACK";

// Average RTT and legend
$rrd_options .= " LINE2:ping$line_color:RTT";
$rrd_options .= ' GPRINT:ping:LAST:%15.2lf GPRINT:min:LAST:%6.2lf';
$rrd_options .= ' GPRINT:max:LAST:%6.2lf GPRINT:ping:AVERAGE:%6.2lf\\n';

// loss line and legend
$rrd_options .= ' AREA:loss#d42e08:Loss';
$rrd_options .= ' GPRINT:loss:LAST:%14.2lf GPRINT:loss:MIN:%6.2lf';
$rrd_options .= ' GPRINT:loss:MAX:%6.2lf GPRINT:loss:AVERAGE:%6.2lf\\n';

// previous time period before this one
if ($graph_params->visible('previous')) {
    $rrd_options .= " COMMENT:' \\n'";
    $rrd_options .= " DEF:pingX=$rrd_filename:avg:AVERAGE:start=$prev_from:end=$from";
    $rrd_options .= " SHIFT:pingX:$period";
    $rrd_options .= " LINE1.25:pingX#CCCCCC:'Prev RTT             '";
    $rrd_options .= ' GPRINT:pingX:AVERAGE:%6.2lf';
    $rrd_options .= " GPRINT:pingX:MAX:%6.2lf  'GPRINT:pingX:AVERAGE:%6.2lf\\n'";
}
