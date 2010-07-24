<?php

$scale_min = "0";

include("includes/graphs/common.inc.php");
$device = device_by_id_cache($id);

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/procurve-mem.rrd";

$rrd_options .= " -b 1024";
$rrd_options .= " DEF:TOTAL=$rrd_filename:TOTAL:AVERAGE";
$rrd_options .= " DEF:FREE=$rrd_filename:FREE:AVERAGE";
$rrd_options .= " DEF:USED=$rrd_filename:USED:AVERAGE";
$rrd_options .= " DEF:FREE_max=$rrd_filename:FREE:MAX";
$rrd_options .= " DEF:USED_max=$rrd_filename:USED:MAX";
$rrd_options .= " DEF:FREE_min=$rrd_filename:FREE:MIN";
$rrd_options .= " DEF:USED_min=$rrd_filename:USED:MIN";
$rrd_options .= " CDEF:tot=FREE,USED,+";

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

$rrd_options .= " LINE1.5:USED#c03030:";
$rrd_options .= " LINE1.5:TOTAL#808080:";

?>
