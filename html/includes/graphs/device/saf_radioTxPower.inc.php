<?php

require 'includes/graphs/common.inc.php';

$rrdfilename = $config['rrd_dir'].'/'.$device['hostname'].'/saf.rrd';

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'                                                  Now        Min         Max\\n'";
    $rrd_options .= ' DEF:radioTxPower='.$rrdfilename.':radioTxPower:AVERAGE ';
    $rrd_options .= " LINE1:radioTxPower#CC0000:'TX Power                                ' ";
    $rrd_options .= ' GPRINT:radioTxPower:LAST:"%3.2lf dBm"';
    $rrd_options .= ' GPRINT:radioTxPower:MIN:"%3.2lf dBm"';
    $rrd_options .= ' GPRINT:radioTxPower:MAX:"%3.2lf dBm\\\l" ';
}
