<?php

// $scale_min = 0;

require 'includes/graphs/common.inc.php';

$rrdfilename = $config['rrd_dir'].'/'.$device['hostname'].'/sub10systems.rrd';


if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'dBm                        Now    Min     Max\\n'";
    $rrd_options .= ' DEF:sub10RadioLclDataRa='.$rrdfilename.':sub10RadioLclDataRa:AVERAGE ';
    $rrd_options .= " LINE1:sub10RadioLclDataRa#CC0000:'Tx Power         ' ";
    $rrd_options .= ' GPRINT:sub10RadioLclDataRa:LAST:%3.2lf ';
    $rrd_options .= ' GPRINT:sub10RadioLclDataRa:MIN:%3.2lf ';
    $rrd_options .= ' GPRINT:sub10RadioLclDataRa:MAX:%3.2lf\\\l ';
}



