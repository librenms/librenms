<?php

require 'includes/graphs/common.inc.php';

$rrd_options .= ' -l 0 -E ';

$rrdfilename = rrd_name($device['hostname'], 'ubnt-airmax-mib');

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dbm                        Now    Min     Max\\n'";
    $rrd_options .= ' DEF:RadioTxPower='.$rrdfilename.':RadioTxPower:AVERAGE ';
    $rrd_options .= " LINE1:RadioTxPower#CC0000:'Tx Power             ' ";
    $rrd_options .= ' GPRINT:RadioTxPower:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:RadioTxPower:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:RadioTxPower:MAX:%3.2lf\\\l ';
}
