<?php

include("includes/graphs/common.inc.php");

$mysql_rrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-mysql-".$app['app_id'].".rrd";

if(is_file($mysql_rrd)) {
    $rrd_filename = $mysql_rrd;
}
$rrd_options .= ' DEF:a='.$rrd_filename.':IDBRDd:AVERAGE ';
$rrd_options .= ' DEF:b='.$rrd_filename.':IDBRId:AVERAGE ';
$rrd_options .= ' DEF:c='.$rrd_filename.':IDBRRd:AVERAGE ';
$rrd_options .= ' DEF:d='.$rrd_filename.':IDBRUd:AVERAGE ';
$rrd_options .= ' CDEF:e=a,b,c,d,+,+,+ ';


$rrd_options .= 'COMMENT:"    Current    Average   Maximum\n" ';

$rrd_options .= 'AREA:a#22FF22:"Deletes"\ \     ';
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:b#0022FF:"Inserts":STACK    ';
$rrd_options .= 'GPRINT:b:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:b:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:c#FF0000:"Reads":STACK    ';
$rrd_options .= 'GPRINT:c:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:c:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:d#00AAAA:"Updates":STACK    ';
$rrd_options .= 'GPRINT:d:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:d:MAX:"%6.2lf %s\n"  ';

$rrd_options .= 'AREA:e#000000:"Total":STACK   ';
$rrd_options .= 'GPRINT:e:LAST:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:e:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= 'GPRINT:e:MAX:"%6.2lf %s\n"  ';

?>
