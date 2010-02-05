<?php

$unit_text = "Load %";

$units='%';
$total_units='%';
$colours='mixed';

$scale_min = "0";
$scale_max = "100";

$nototal = 1;

include("common.inc.php");
$database = $config['rrd_dir'] . "/" . $hostname . "/junos-cpu.rrd";

$rrd_options .= " DEF:load=$database:LOAD:AVERAGE";
$rrd_options .= " DEF:load_max=$database:LOAD:MAX";
$rrd_options .= " DEF:load_min=$database:LOAD:MIN";
$rrd_options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ Current\ \ Minimum\ \ Maximum\ \ Average\\\\n";
$rrd_options .= " AREA:load#ffee99: LINE1.25:load#aa2200:Load\ %";
$rrd_options .= " GPRINT:load:LAST:%6.2lf\  GPRINT:load_min:AVERAGE:%6.2lf\ ";
$rrd_options .= " GPRINT:load_max:MAX:%6.2lf\  GPRINT:load:AVERAGE:%6.2lf\\\\n";

?>
