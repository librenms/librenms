<?php

include("includes/graphs/common.inc.php");

$nginx_rrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-nginx-".$app['app_id'].".rrd";

if (is_file($nginx_rrd))
{
  $rrd_filename = $nginx_rrd;
}

$rrd_options .= ' -b 1000 ';
$rrd_options .= ' -l 0 ';
$rrd_options .= ' DEF:a='.$rrd_filename.':Requests:AVERAGE ';

$rrd_options .= 'COMMENT:"Requests    Current    Average   Maximum\n" ';

$rrd_options .= "LINE2:a#22FF22:'Requests  '";
$rrd_options .= 'GPRINT:a:LAST:"%6.2lf %s" ';
$rrd_options .= 'GPRINT:a:AVERAGE:"%6.2lf %s" ';
$rrd_options .= 'GPRINT:a:MAX:"%6.2lf %s\n"  ';

?>
