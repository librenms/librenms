<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = rrd_name($device['hostname'], 'gaia_connections');
$rrd_options .= " --vertical-label='Connections'";

$rrd_options .= " DEF:connections=$rrd_filename:NumConn:LAST";

$rrd_options .= " LINE1.5:connections#cc0000:'" . rrdtool_escape('Active connections') . "'";
$rrd_options .= " 'GPRINT:connections:LAST:Current\:%4.0lf'";
$rrd_options .= " 'GPRINT:connections:AVERAGE:Average\:%4.0lf'";
$rrd_options .= " 'GPRINT:connections:MAX:Max\:%4.0lf\l'";
