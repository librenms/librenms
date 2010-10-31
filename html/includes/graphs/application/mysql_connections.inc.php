<?php

include("includes/graphs/common.inc.php");

$mysql_rrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-mysql-".$app['app_id'].".rrd";

if(is_file($mysql_rrd)) {
    $rrd_filename = $mysql_rrd;
}
$rrd_options .= ' -b 1000 ';
$rrd_options .= ' DEF:a='.$rrd_filename.':MaCs:AVERAGE ';
$rrd_options .= ' DEF:b='.$rrd_filename.':MUCs:AVERAGE ';
$rrd_options .= ' DEF:c='.$rrd_filename.':ACs:AVERAGE ';
$rrd_options .= ' DEF:d='.$rrd_filename.':AdCs:AVERAGE ';
$rrd_options .= ' DEF:e='.$rrd_filename.':TCd:AVERAGE ';
$rrd_options .= ' DEF:f='.$rrd_filename.':Cs:AVERAGE ';

$rrd_options .= 'COMMENT:"    Current    Average   Maximum\n" ';

$rrd_options .= 'AREA:a#cdcfc4:"Max Connections"\ \     ';
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:b#FFD660:"Max Used Connections"\ \   ';
$rrd_options .= 'GPRINT:b:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE1:c#22FF22:"Aborted Clients"\ \   ';
$rrd_options .= 'GPRINT:c:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE1:d#0022FF:"Aborted Connects"\ \    ';
$rrd_options .= 'GPRINT:d:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE:e#FF0000:"Threads Connected"\ \  ';
$rrd_options .= 'GPRINT:e:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:e:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:e:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE1:f#00AAAA:"New Connections"\ \   ';
$rrd_options .= 'GPRINT:f:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:f:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:f:MAX:"%6.2lf %s\n"  ';


?>
