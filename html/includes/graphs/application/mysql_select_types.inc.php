<?php

include("includes/graphs/common.inc.php");

$mysql_rrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-mysql-".$app['app_id'].".rrd";

if (is_file($mysql_rrd))
{
  $rrd_filename = $mysql_rrd;
}

$rrd_options .= ' -b 1024 ';
$rrd_options .= ' -l 0 ';
$rrd_options .= ' DEF:a='.$rrd_filename.':SFJn:AVERAGE ';
$rrd_options .= ' DEF:b='.$rrd_filename.':SFRJn:AVERAGE ';
$rrd_options .= ' DEF:c='.$rrd_filename.':SRe:AVERAGE ';
$rrd_options .= ' DEF:d='.$rrd_filename.':SRCk:AVERAGE ';
$rrd_options .= ' DEF:e='.$rrd_filename.':SSn:AVERAGE ';
$rrd_options .= ' CDEF:total=a,b,c,d,e,+,+,+,+ ';

$rrd_options .= 'COMMENT:"    Current    Average   Maximum\n" ';

$rrd_options .= 'AREA:a#22FF22:"Full Join"\ \     ';
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:b#0022FF:"Full Range"\ \     ';
$rrd_options .= 'GPRINT:b:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:c#FF0000:"Range"\ \     ';
$rrd_options .= 'GPRINT:c:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:d#00AAAA:"Range Check"\ \     ';
$rrd_options .= 'GPRINT:d:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:e#FF00FF:"Scan"\ \     ';
$rrd_options .= 'GPRINT:e:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:e:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:e:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:total#000000:"Total"\ \     ';
$rrd_options .= 'GPRINT:total:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:total:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:total:MAX:"%6.2lf %s\n"  ';

?>