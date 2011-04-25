<?php

include("includes/graphs/common.inc.php");

$rrd_options .= " -u 100 -l 0 -E -b 1024 ";

$iter = "1";

$rrd_options .= " COMMENT:'                                Used\\n'";

$colour="CC0000";
$colour_area="ffaaaa";

$descr = substr(str_pad(short_hrDeviceDescr($mempool['mempool_descr']), 24),0,24);
$descr = str_replace(":", "\:", $descr);

$perc  = round($mempool['mempool_perc'], 0);
$background = get_percentage_colours($perc);

$rrd_options .= " DEF:$mempool[mempool_id]used=$rrd_filename:used:AVERAGE";
$rrd_options .= " DEF:$mempool[mempool_id]free=$rrd_filename:free:AVERAGE";
$rrd_options .= " CDEF:$mempool[mempool_id]size=$mempool[mempool_id]used,$mempool[mempool_id]free,+";
$rrd_options .= " CDEF:$mempool[mempool_id]perc=$mempool[mempool_id]used,$mempool[mempool_id]size,/,100,*";
$rrd_options .= " AREA:$mempool[mempool_id]perc#" . $background['right'] . ":";
$rrd_options .= " LINE1.25:$mempool[mempool_id]perc#" . $background['left'] . ":'$descr'";
$rrd_options .= " GPRINT:$mempool[mempool_id]size:LAST:%6.2lf%sB";
$rrd_options .= " GPRINT:$mempool[mempool_id]free:LAST:%6.2lf%sB";
$rrd_options .= " GPRINT:$mempool[mempool_id]perc:LAST:%5.2lf%%\\\\n";

?>
