<?php

include("includes/graphs/common.inc.php");

$mysql_rrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-mysql-".$app['app_id'].".rrd";

if (is_file($mysql_rrd))
{
  $rrd_filename = $mysql_rrd;
}

$rrd_options .= ' -b 1000 ';
$rrd_options .= ' DEF:a='.$rrd_filename.':IBTNx:AVERAGE ';

$rrd_options .= 'COMMENT:"    Current    Average   Maximum\n" ';

$rrd_options .= 'LINE1:a#22FF22:"Transactions created"\ \     ';
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s"  ';

?>