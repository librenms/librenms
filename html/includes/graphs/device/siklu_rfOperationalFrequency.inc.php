<?php

require 'includes/graphs/common.inc.php';

$rrdfilename = rrd_name($device['hostname'], 'siklu-wireless');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'Hz                        Now    Min     Max\\n'";
    $rrd_options .= ' DEF:rfOperFreq='.$rrdfilename.':rfOperFreq:AVERAGE ';
    $rrd_options .= " LINE1:rfOperFreq#CC0000:'GHz                 ' ";
    $rrd_options .= ' GPRINT:rfOperFreq:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:rfOperFreq:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:rfOperFreq:MAX:%3.2lf\\\l ';
}
