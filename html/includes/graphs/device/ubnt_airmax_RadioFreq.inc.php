<?php

require 'includes/graphs/common.inc.php';

$rrd_options .= ' -l 0 -E ';

$rrdfilename = rrd_name($device['hostname'], 'ubnt-airmax-mib');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'                           Now    Min     Max\\n'";
    $rrd_options .= ' DEF:RadioFreq='.$rrdfilename.':RadioFreq:AVERAGE ';
    $rrd_options .= " LINE1:RadioFreq#CC0000:'Frequency            ' ";
    $rrd_options .= ' GPRINT:RadioFreq:LAST:%3.2lf%s ';
    $rrd_options .= ' GPRINT:RadioFreq:MIN:%3.2lf%s ';
    $rrd_options .= ' GPRINT:RadioFreq:MAX:%3.2lf%s\\\l ';
}
