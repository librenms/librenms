<?php

$scale_min = "0";

include("includes/graphs/common.inc.php");

$rrd_options .= " COMMENT:'                         Last     Max\\n'";

$sensor['sensor_descr_fixed'] = substr(str_pad($sensor['sensor_descr'], 20),0,20);

$rrd_options .= " DEF:sensor=$rrd_filename:sensor:AVERAGE";
$rrd_options .= " LINE1.5:sensor#cc0000:'" . str_replace(':','\:',str_replace('\*','*',quotemeta($sensor['sensor_descr_fixed'])))."'"; # Ugly hack :(
$rrd_options .= " GPRINT:sensor:LAST:%3.0lfrpm";
$rrd_options .= " GPRINT:sensor:MAX:%3.0lfrpm\\\\l";

if (is_numeric($sensor['sensor_limit'])) $rrd_options .= " HRULE:".$sensor['sensor_limit']."#999999::dashes";
if (is_numeric($sensor['sensor_limit_low'])) $rrd_options .= " HRULE:".$sensor['sensor_limit_low']."#999999::dashes";

?>
