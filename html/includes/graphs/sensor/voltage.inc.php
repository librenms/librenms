<?php

$scale_min = "0";

include("includes/graphs/common.inc.php");

$rrd_options .= " -A ";

$rrd_options .= " COMMENT:'                           Last    Max\\n'";

$sensor['sensor_descr_fixed'] = substr(str_pad($sensor['sensor_descr'], 22),0,22);
$sensor['sensor_descr_fixed'] = str_replace(':','\:',str_replace('\*','*',$sensor['sensor_descr_fixed']));

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/volt-" . safename($sensor['sensor_type']."-".$sensor['sensor_index']) . ".rrd";

$rrd_options .= " DEF:volt=$rrd_filename:volt:AVERAGE";
$rrd_options .= " DEF:volt_max=$rrd_filename:volt:MAX";
$rrd_options .= " DEF:volt_min=$rrd_filename:volt:MIN";

$rrd_options .= " AREA:volt_max#c5c5c5";
$rrd_options .= " AREA:volt_min#ffffffff";

#$rrd_options .= " AREA:volt#FFFF99";
$rrd_options .= " LINE1.5:volt#cc0000:'" . $sensor['sensor_descr_fixed']."'";
$rrd_options .= " GPRINT:volt:LAST:%3.2lfV";
$rrd_options .= " GPRINT:volt:MAX:%3.2lfV\\\\l";

?>
