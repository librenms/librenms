<?php

include("includes/graphs/common.inc.php");

$mysql_rrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-mysql-".$app['app_id'].".rrd";

if (is_file($mysql_rrd))
{
    $rrd_filename = $mysql_rrd;
}

$rrd_options .= ' -b 1024 ';
$rrd_options .= ' DEF:a='.$rrd_filename.':CDe:AVERAGE ';
$rrd_options .= ' DEF:b='.$rrd_filename.':CIt:AVERAGE ';
$rrd_options .= ' DEF:c='.$rrd_filename.':CISt:AVERAGE ';
$rrd_options .= ' DEF:d='.$rrd_filename.':CLd:AVERAGE ';
$rrd_options .= ' DEF:e='.$rrd_filename.':CRe:AVERAGE ';
$rrd_options .= ' DEF:f='.$rrd_filename.':CRSt:AVERAGE ';
$rrd_options .= ' DEF:g='.$rrd_filename.':CSt:AVERAGE ';
$rrd_options .= ' DEF:h='.$rrd_filename.':CUe:AVERAGE ';
$rrd_options .= ' DEF:i='.$rrd_filename.':CUMi:AVERAGE ';

$rrd_options .= 'COMMENT:"    Current    Average   Maximum\n" ';

$rrd_options .= "AREA:a#22FF22:Delete\ \     ";
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:b#0022FF:"Insert ":STACK    ';
$rrd_options .= 'GPRINT:b:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:c#FF0000:"Insert Select":STACK    ';
$rrd_options .= 'GPRINT:c:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:d#00AAAA:"Load Data":STACK    ';
$rrd_options .= 'GPRINT:d:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:e#FF00FF:"Replace ":STACK    ';
$rrd_options .= 'GPRINT:e:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:e:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:e:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:f#FFA500:"Replace Select":STACK    ';
$rrd_options .= 'GPRINT:f:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:f:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:f:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:g#CC0000:"Select ":STACK   ';
$rrd_options .= 'GPRINT:g:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:g:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:g:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:h#0000CC:"Update ":STACK   ';
$rrd_options .= 'GPRINT:h:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:h:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:h:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:i#0080C0:"Update Multi":STACK    ';
$rrd_options .= 'GPRINT:i:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:i:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:i:MAX:"%6.2lf %s\n"  ';

?>