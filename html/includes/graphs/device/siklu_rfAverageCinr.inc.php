<?php

require 'includes/graphs/common.inc.php';

$rrdfilename = rrd_name($device['hostname'], 'siklu-wireless');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'db                        Now    Min     Max\\n'";
    $rrd_options .= ' DEF:rfAverageCinr='.$rrdfilename.':rfAverageCinr:AVERAGE ';
    $rrd_options .= " LINE1:rfAverageCinr#CC0000:'CINR                 ' ";
    $rrd_options .= ' GPRINT:rfAverageCinr:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:rfAverageCinr:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:rfAverageCinr:MAX:%3.2lf\\\l ';
}
