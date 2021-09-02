<?php

/*
 * NTTCOM MS module for printing CheckPoint SecurePlatform sessions
 */

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'secureplatform_sessions');

$rrd_options .= " DEF:connections=$rrd_filename:NumConn:AVERAGE";
$rrd_options .= " DEF:connections_max=$rrd_filename:NumConn:MAX";
$rrd_options .= " DEF:connections_min=$rrd_filename:NumConn:MIN";
$rrd_options .= ' AREA:connections_min';

$rrd_options .= " LINE1.5:connections#cc0000:'Current connections'";
$rrd_options .= ' GPRINT:connections_min:MIN:%4.0lf';
$rrd_options .= ' GPRINT:connections:LAST:%4.0lf';
$rrd_options .= ' GPRINT:connections_max:MAX:%4.0lf\l';
