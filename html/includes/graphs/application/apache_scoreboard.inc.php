<?php

$scale_min = 0;

include("includes/graphs/common.inc.php");

$apache_rrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-apache-".$app['app_id'].".rrd";

if (is_file($apache_rrd))
{
 $rrd_filename = $apache_rrd;
}

$rrd_options .= ' DEF:a='.$rrd_filename.':sb_wait:AVERAGE ';
$rrd_options .= 'DEF:b='.$rrd_filename.':sb_start:AVERAGE ';
$rrd_options .= 'DEF:c='.$rrd_filename.':sb_reading:AVERAGE ';
$rrd_options .= 'DEF:d='.$rrd_filename.':sb_writing:AVERAGE ';
$rrd_options .= 'DEF:e='.$rrd_filename.':sb_keepalive:AVERAGE ';
$rrd_options .= 'DEF:f='.$rrd_filename.':sb_dns:AVERAGE ';
$rrd_options .= 'DEF:g='.$rrd_filename.':sb_closing:AVERAGE ';
$rrd_options .= 'DEF:h='.$rrd_filename.':sb_logging:AVERAGE ';
$rrd_options .= 'DEF:i='.$rrd_filename.':sb_graceful:AVERAGE ';
$rrd_options .= 'DEF:j='.$rrd_filename.':sb_idle:AVERAGE ';
$rrd_options .= 'COMMENT:"Scoreboard    Current    Average   Maximum\n" ';
$rrd_options .= 'AREA:a#4444FFFF:"Waiting   "  ';
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\n"  ';
$rrd_options .= 'AREA:b#FF0000FF:"Keepalive ":STACK ';
$rrd_options .= 'GPRINT:b:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:MAX:"%6.2lf %s\n"  ';
$rrd_options .= 'AREA:c#750F7DFF:"Reading   ":STACK ';
$rrd_options .= 'GPRINT:c:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:MAX:"%6.2lf %s\n"  ';
$rrd_options .= 'AREA:d#00FF00FF:"Sending   ":STACK ';
$rrd_options .= 'GPRINT:d:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:AVERAGE":%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:MAX:"%6.2lf %s\n"  ';
$rrd_options .= 'AREA:e#157419FF:"Starting  ":STACK ';
$rrd_options .= 'GPRINT:e:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:e:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:e:MAX:"%6.2lf %s\n"  ';
$rrd_options .= 'AREA:f#6DC8FEFF:"DNS Lookup":STACK ';
$rrd_options .= 'GPRINT:f:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:f:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:f:MAX:"%6.2lf %s\n"  ';
$rrd_options .= 'AREA:g#FFAB00FF:"Closing   ":STACK ';
$rrd_options .= 'GPRINT:g:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:g:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:g:MAX:"%6.2lf %s\n"  ';
$rrd_options .= 'AREA:h#FFFF00FF:"Logging   ":STACK ';
$rrd_options .= 'GPRINT:h:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:h:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:h:MAX:"%6.2lf %s\n"  ';
$rrd_options .= 'AREA:i#FF5576FF:"Graceful  ":STACK ';
$rrd_options .= 'GPRINT:i:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:i:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:i:MAX:"%6.2lf %s\n"  ';
$rrd_options .= 'AREA:j#FF4105FF:"Idle      ":STACK ';
$rrd_options .= 'GPRINT:j:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:j:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:j:MAX:"%6.2lf %s\n"';

?>
