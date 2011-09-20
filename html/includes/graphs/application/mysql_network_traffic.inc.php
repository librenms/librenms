<?php

include("includes/graphs/common.inc.php");

$mysql_rrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-mysql-".$app['app_id'].".rrd";

if (is_file($mysql_rrd))
{
  $rrd_filename = $mysql_rrd;
}

$rrd_options .= ' -b 1024 ';
$rrd_options .= ' DEF:a='.$rrd_filename.':BRd:AVERAGE ';
$rrd_options .= ' DEF:b='.$rrd_filename.':BSt:AVERAGE ';
$rrd_options .= ' CDEF:c=a,-1,* ';

$rrd_options .= 'COMMENT:"    Current    Average   Maximum\n" ';

$rrd_options .= 'LINE2:c#00AA00:"Bytes read"\ \     ';
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE2:b#00AA00:"Bytes sent"\ \     ';
$rrd_options .= 'GPRINT:b:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:MAX:"%6.2lf %s\n"  ';

?>