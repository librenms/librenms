<?php

$scale_min = '0';

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'ucd_load');

$rrd_options .= " DEF:1min=$rrd_filename:1min:AVERAGE";
$rrd_options .= " DEF:5min=$rrd_filename:5min:AVERAGE";
$rrd_options .= " DEF:15min=$rrd_filename:15min:AVERAGE";
$rrd_options .= ' CDEF:a=1min,100,/';
$rrd_options .= ' CDEF:b=5min,100,/';
$rrd_options .= ' CDEF:c=15min,100,/';
$rrd_options .= ' CDEF:cdefd=a,b,c,+,+';
$rrd_options .= " COMMENT:'Load Average  Current    Average    Maximum\\n'";
$rrd_options .= " AREA:a#ffeeaa:'1 Min'";
$rrd_options .= ' LINE1:a#c5aa00:';
$rrd_options .= " GPRINT:a:LAST:'    %7.2lf'";
$rrd_options .= " GPRINT:a:AVERAGE:'  %7.2lf'";
$rrd_options .= " GPRINT:a:MAX:'  %7.2lf\\n'";
$rrd_options .= " LINE1.25:b#ea8f00:'5 Min'";
$rrd_options .= " GPRINT:b:LAST:'    %7.2lf'";
$rrd_options .= " GPRINT:b:AVERAGE:'  %7.2lf'";
$rrd_options .= " GPRINT:b:MAX:'  %7.2lf\\n'";
$rrd_options .= " LINE1.25:c#cc0000:'15 Min'";
$rrd_options .= " GPRINT:c:LAST:'   %7.2lf'";
$rrd_options .= " GPRINT:c:AVERAGE:'  %7.2lf'";
$rrd_options .= " GPRINT:c:MAX:'  %7.2lf\\n'";
