<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'gaia_logserver_lograte');
$rrd_options .= " --vertical-label='Logs per second'";

$rrd_options .= " DEF:lograte=$rrd_filename:LogReceiveRate:LAST";

$rrd_options .= " LINE1.5:lograte#cc0000:'" . Rrd::safeDescr('Log Rate') . "'";
$rrd_options .= " 'GPRINT:lograte:LAST:Current\:%4.0lf'";
$rrd_options .= " 'GPRINT:lograte:AVERAGE:Average\:%4.0lf'";
$rrd_options .= " 'GPRINT:lograte:MAX:Max\:%4.0lf\l'";
