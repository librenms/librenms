<?php

$hostname = (isset($_GET['hostname']) ? $_GET['hostname'] : 'unkown');
$rrd_filename = Rrd::name($device['hostname'], ['app', 'shoutcast', $app['app_id'], $hostname]);

require 'includes/html/graphs/common.inc.php';

$rrd_options .= ' DEF:cur=' . $rrd_filename . ':current:AVERAGE';
$rrd_options .= ' DEF:max=' . $rrd_filename . ':max:MAX';
// $rrd_options .= " DEF:bit=".$rrd_filename.":bitrate:LAST";
$rrd_options .= ' DEF:bit=' . $rrd_filename . ':bitrate:MAX';
$rrd_options .= ' DEF:peak=' . $rrd_filename . ':peak:MAX';
$rrd_options .= ' DEF:unique=' . $rrd_filename . ':unique:AVERAGE';
$rrd_options .= ' DEF:status=' . $rrd_filename . ':status:AVERAGE';
$rrd_options .= ' CDEF:peakm=peak,1,-';
$rrd_options .= ' VDEF:avg=cur,AVERAGE';
$rrd_options .= ' VDEF:peakh=peakm,MAXIMUM';
$rrd_options .= ' CDEF:bitrate=bit,8,*';
$rrd_options .= ' CDEF:server=status,UN,1,0,IF';
$rrd_options .= ' CDEF:server_offline=status,1,LT,1,UNKN,IF';
$rrd_options .= ' CDEF:stream=max,UN,1,0,IF';
$rrd_options .= ' CDEF:stream_offline=max,1,LT,1,UNKN,IF';
$rrd_options .= ' AREA:cur#63C2FEFF:"Current Listeners"';

if ($width >= 355) {
    $rrd_options .= ' GPRINT:cur:LAST:"\:%8.2lf"';
    $rrd_options .= ' GPRINT:max:LAST:"from%8.2lf"';
    $rrd_options .= ' GPRINT:bitrate:LAST:"(bitrate\:%8.2lf%s"';
    $rrd_options .= ' COMMENT:")\\n"';
} else {
    $rrd_options .= " GPRINT:cur:LAST:\"\:%8.2lf\\n\"";
}

$rrd_options .= ' AREA:unique#AADEFEFF:"Unique Listeners "';
$rrd_options .= " GPRINT:unique:LAST:\"\:%8.2lf%s\\n\"";
$rrd_options .= ' HRULE:avg#FF9000FF:"Average Listeners"';
$rrd_options .= " GPRINT:avg:\"\:%8.2lf\\n\"";
$rrd_options .= ' LINE1:peak#C000FFFF:"Peak Listeners   "';
$rrd_options .= " GPRINT:peak:LAST:\"\:%8.2lf\\n\"";
$rrd_options .= ' TICK:stream_offline#B4FF00FF:1.0:"Streaming client offline\\n"';
$rrd_options .= ' TICK:server_offline' . \LibreNMS\Config::get('warn_colour_alt') . 'FF:1.0:"Streaming server offline"';
