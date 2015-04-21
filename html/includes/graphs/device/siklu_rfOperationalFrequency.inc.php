<?php

include("includes/graphs/common.inc.php");

$rrdfilename  = $config['rrd_dir'] . "/".$device['hostname']."/siklu-wireless.rrd";

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'Hz                        Now    Min     Max\\n'";
    $rrd_options .= " DEF:rfOperFreq=".$rrdfilename.":rfOperFreq:AVERAGE ";
    $rrd_options .= " LINE1:rfOperFreq#CC0000:'RSSI                 ' ";
    $rrd_options .= " GPRINT:rfOperFreq:LAST:%3.2lf ";
    $rrd_options .= " GPRINT:rfOperFreq:MIN:%3.2lf ";
    $rrd_options .= " GPRINT:rfOperFreq:MAX:%3.2lf\\\l ";
}

