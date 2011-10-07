<?php

include("includes/graphs/common.inc.php");

$nginx_rrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-nginx-".$app['app_id'].".rrd";

if (is_file($nginx_rrd))
{
  $rrd_filename = $nginx_rrd;
}

$rrd_options .= ' DEF:a='.$rrd_filename.':Active:AVERAGE ';
$rrd_options .= ' DEF:b='.$rrd_filename.':Reading:AVERAGE ';
$rrd_options .= ' DEF:c='.$rrd_filename.':Writing:AVERAGE ';
$rrd_options .= ' DEF:d='.$rrd_filename.':Waiting:AVERAGE ';

$rrd_options .= ' COMMENT:"Connections    Current    Average   Maximum\n" ';

$rrd_options .= " LINE1:a#22FF22:'Active      '";
$rrd_options .= ' GPRINT:a:LAST:"%6.2lf %s"  ';
$rrd_options .= ' GPRINT:a:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= ' GPRINT:a:MAX:"%6.2lf %s\n"  ';

$rrd_options .= ' LINE1.25:b#0022FF:Reading    ';
$rrd_options .= ' GPRINT:b:LAST:"%6.2lf %s"  ';
$rrd_options .= ' GPRINT:b:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= ' GPRINT:b:MAX:"%6.2lf %s\n"  ';

$rrd_options .= ' LINE1.25:c#FF0000:Writing    ';
$rrd_options .= ' GPRINT:c:LAST:"%6.2lf %s"  ';
$rrd_options .= ' GPRINT:c:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= ' GPRINT:c:MAX:"%6.2lf %s\n"  ';

$rrd_options .= ' LINE1.25:d#00AAAA:Waiting    ';
$rrd_options .= ' GPRINT:d:LAST:"%6.2lf %s"  ';
$rrd_options .= ' GPRINT:d:AVERAGE:"%6.2lf %s"  ';
$rrd_options .= ' GPRINT:d:MAX:"%6.2lf %s\n"  ';

?>
