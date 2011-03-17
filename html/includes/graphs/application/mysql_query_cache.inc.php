<?php

include("includes/graphs/common.inc.php");

$mysql_rrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-mysql-".$app['app_id'].".rrd";

if (is_file($mysql_rrd))
{
  $rrd_filename = $mysql_rrd;
}

$rrd_options .= ' -b 1024 ';
$rrd_options .= ' DEF:a='.$rrd_filename.':QCQICe:AVERAGE ';
$rrd_options .= ' DEF:b='.$rrd_filename.':QCHs:AVERAGE ';
$rrd_options .= ' DEF:c='.$rrd_filename.':QCIs:AVERAGE ';
$rrd_options .= ' DEF:d='.$rrd_filename.':QCNCd:AVERAGE ';
$rrd_options .= ' DEF:e='.$rrd_filename.':QCLMPs:AVERAGE ';

$rrd_options .= 'COMMENT:"    Current    Average   Maximum\n" ';

$rrd_options .= 'LINE2:a#22FF22:"Queries in cache"\ \     ';
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE2:b#0022FF:"Cache hits"\ \     ';
$rrd_options .= 'GPRINT:b:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE2:c#FF0000:"Inserts"\ \     ';
$rrd_options .= 'GPRINT:c:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE2:d#00AAAA:"Not cached"\ \     ';
$rrd_options .= 'GPRINT:d:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE2:e#FF00FF:"Low-memory prunes"\ \     ';
$rrd_options .= 'GPRINT:e:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:e:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:e:MAX:"%6.2lf %s\n"  ';

?>