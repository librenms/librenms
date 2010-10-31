<?php

include("includes/graphs/common.inc.php");

$mysql_rrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-mysql-".$app['app_id'].".rrd";

if(is_file($mysql_rrd)) {
    $rrd_filename = $mysql_rrd;
}
$rrd_options .= ' DEF:a='.$rrd_filename.':IBILog:AVERAGE ';
$rrd_options .= ' DEF:b='.$rrd_filename.':IBISc:AVERAGE ';
$rrd_options .= ' DEF:c='.$rrd_filename.':IBIFLg:AVERAGE ';
$rrd_options .= ' DEF:d='.$rrd_filename.':IBFBl:AVERAGE ';
$rrd_options .= ' DEF:e='.$rrd_filename.':IBIIAo:AVERAGE ';
$rrd_options .= ' DEF:f='.$rrd_filename.':IBIAd:AVERAGE ';
$rrd_options .= ' DEF:g='.$rrd_filename.':IBIAe:AVERAGE ';


$rrd_options .= 'COMMENT:"    Current    Average   Maximum\n" ';

$rrd_options .= 'LINE1:a#22FF22:"AIO Log"\ \     ';
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE1:b#0022FF:"AIO Sync"\ \   ';
$rrd_options .= 'GPRINT:b:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE1:c#FF0000:"Buf Pool Flush"\ \   ';
$rrd_options .= 'GPRINT:c:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE1:d#00AAAA:"Log Flushes"\ \   ';
$rrd_options .= 'GPRINT:d:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE1:e#FF00FF:"Insert Buf AIO Read"\ \   ';
$rrd_options .= 'GPRINT:e:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:e:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:e:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE1:f#FFA500:"Normal AIO Reads"\ \   ';
$rrd_options .= 'GPRINT:f:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:f:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:f:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'LINE1:d#CC0000:"Normal AIO Writes"\ \   ';
$rrd_options .= 'GPRINT:g:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:g:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:g:MAX:"%6.2lf %s\n"  ';
?>
