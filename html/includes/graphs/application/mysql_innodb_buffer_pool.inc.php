<?php

include("includes/graphs/common.inc.php");

$mysql_rrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-mysql-".$app['app_id'].".rrd";

if(is_file($mysql_rrd)) {
    $rrd_filename = $mysql_rrd;
}
$rrd_options .= ' -b 1024 ';
$rrd_options .= ' DEF:a='.$rrd_filename.':IBPse:AVERAGE ';
$rrd_options .= ' DEF:b='.$rrd_filename.':IBPDBp:AVERAGE ';
$rrd_options .= ' DEF:c='.$rrd_filename.':IBPFe:AVERAGE ';
$rrd_options .= ' DEF:d='.$rrd_filename.':IBPMps:AVERAGE ';

$rrd_options .= 'COMMENT:"    Current    Average   Maximum\n" ';

$rrd_options .= 'AREA:a#FFD660:"Buffer Pool Size"\ \     ';
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:b#CDCFC4:"Database Pages"\ \   ';
$rrd_options .= 'GPRINT:b:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE2:c#22FF22:"Free Pages"\ \   ';
$rrd_options .= 'GPRINT:c:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE2:d#0022FF:"Modified Pages"\ \    ';
$rrd_options .= 'GPRINT:d:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:MAX:"%6.2lf %s\n"  ';


?>
