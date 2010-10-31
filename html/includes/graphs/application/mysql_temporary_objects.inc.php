<?php

include("includes/graphs/common.inc.php");

$mysql_rrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-mysql-".$app['app_id'].".rrd";

if(is_file($mysql_rrd)) {
    $rrd_filename = $mysql_rrd;
}

/* $rrd_options .= ' -b 1024 '; */
$rrd_options .= ' DEF:a='.$rrd_filename.':CTMPDTs:AVERAGE ';
$rrd_options .= ' DEF:b='.$rrd_filename.':CTMPTs:AVERAGE ';
$rrd_options .= ' DEF:c='.$rrd_filename.':CTMPFs:AVERAGE ';


$rrd_options .= 'COMMENT:"    Current    Average   Maximum\n" ';

$rrd_options .= 'LINE2:a#22FF22:"Temp disk tables"\ \     ';
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE2:b#0022FF:"Temp tables"\ \     ';
$rrd_options .= 'GPRINT:b:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE2:c#FF0000:"Temp files"\ \     ';
$rrd_options .= 'GPRINT:c:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:MAX:"%6.2lf %s\n"  ';



?>
