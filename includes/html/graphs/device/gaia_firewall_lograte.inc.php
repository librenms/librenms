<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = rrd_name($device['hostname'], 'gaia_fw_lograte');
$rrd_options .= " --vertical-label='Logs per second'";

$rrd_options .= " DEF:fw_lograte=$rrd_filename:fwlograte:LAST";

$rrd_options .= " LINE1.5:fw_lograte#cc0000:'" . rrdtool_escape('Log Rate') . "'";
$rrd_options .= " 'GPRINT:fw_lograte:LAST:Current\:%4.0lf'";
$rrd_options .= " 'GPRINT:fw_lograte:AVERAGE:Average\:%4.0lf'";
$rrd_options .= " 'GPRINT:fw_lograte:MAX:Max\:%4.0lf\l'";
