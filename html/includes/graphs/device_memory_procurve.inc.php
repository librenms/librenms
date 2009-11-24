<?php

include("common.inc.php");

$rrd_filename = $config['rrd_dir'] . "/" . $hostname . "/procurve-mem.rrd";

$rrd_options .= " -b 1024";
$rrd_options .= " DEF:TOTAL=$rrd_filename:TOTAL:AVERAGE";
$rrd_options .= " DEF:FREE=$rrd_filename:FREE:AVERAGE";
$rrd_options .= " DEF:USED=$rrd_filename:USED:AVERAGE";

$rrd_options .= " COMMENT:'Bytes       Current    Average     Maximum\\n'";

$rrd_options .= " LINE1:USED#d0b080:";
$rrd_options .= " AREA:USED#f0e0a0:used";
$rrd_options .= " GPRINT:USED:LAST:\ \ \ %7.2lf%sB";
$rrd_options .= " GPRINT:USED:AVERAGE:%7.2lf%sB";
$rrd_options .= " GPRINT:USED:MAX:%7.2lf%sB\\\\n";
$rrd_options .= " AREA:FREE#e5e5e5:free:STACK";
$rrd_options .= " GPRINT:FREE:LAST:\ \ \ %7.2lf%sB";
$rrd_options .= " GPRINT:FREE:AVERAGE:%7.2lf%sB";
$rrd_options .= " GPRINT:FREE:MAX:%7.2lf%sB\\\\n";
$rrd_options .= " LINE1:TOTAL#e5e5e5:";

?>
