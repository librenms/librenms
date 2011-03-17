<?php

include("includes/graphs/common.inc.php");

$mysql_rrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-mysql-".$app['app_id'].".rrd";

if (is_file($mysql_rrd))
{
  $rrd_filename = $mysql_rrd;
}

$rrd_options .= ' -b 1000 ';
$rrd_options .= ' DEF:a='.$rrd_filename.':SRows:AVERAGE ';
$rrd_options .= ' DEF:b='.$rrd_filename.':SRange:AVERAGE ';
$rrd_options .= ' DEF:c='.$rrd_filename.':SMPs:AVERAGE ';
$rrd_options .= ' DEF:d='.$rrd_filename.':SScan:AVERAGE ';

$rrd_options .= 'COMMENT:"\t           Current    Average   Maximum\n" ';

$rrd_options .= 'LINE2:a#22FF22:"Rows Sorted"\ \     ';
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s" ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s" ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\n"  ';

$rrd_options .= "LINE2:b#0022FF:Range\ \     ";
$rrd_options .= 'GPRINT:b:LAST:"%6.2lf %s" ';
$rrd_options .= 'GPRINT:b:AVERAGE:"%6.2lf %s" ';
$rrd_options .= 'GPRINT:b:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE2:c#FF0000:"Merge Passes"\ \     ';
$rrd_options .= 'GPRINT:c:LAST:"%6.2lf %s" ';
$rrd_options .= 'GPRINT:c:AVERAGE:"%6.2lf %s" ';
$rrd_options .= 'GPRINT:c:MAX:"%6.2lf %s\n"  ';

$rrd_options .= "LINE2:d#FF0000:Scan\ \     ";
$rrd_options .= 'GPRINT:d:LAST:"%6.2lf %s" ';
$rrd_options .= 'GPRINT:d:AVERAGE:"%6.2lf %s" ';
$rrd_options .= 'GPRINT:d:MAX:"%6.2lf %s\n"  ';

?>