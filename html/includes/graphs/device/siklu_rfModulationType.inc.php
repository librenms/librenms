<?php

include("includes/graphs/common.inc.php");

$rrdfilename  = $config['rrd_dir'] . "/".$device['hostname']."/siklu-mib.rrd";

if (file_exists($rrdfilename)) {
    $rrd_options .= " COMMENT:'                        Now    Min     Max\\n'";
    $rrd_options .= " DEF:rfModulationType=".$rrdfilename.":rfModulationType:AVERAGE ";
    $rrd_options .= " LINE1:rfModulationType#CC0000:'Modulation                 ' ";
    $rrd_options .= " GPRINT:rfModulationType:LAST:%3.2lf ";
    $rrd_options .= " GPRINT:rfModulationType:MIN:%3.2lf ";
    $rrd_options .= " GPRINT:rfModulationType:MAX:%3.2lf\\\l ";
}

