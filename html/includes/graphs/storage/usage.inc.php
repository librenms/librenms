<?php

$scale_min = "0";
$scale_max = "100";

include("includes/graphs/common.inc.php");

$rrd_options .= " -b 1024";

$iter = "1";

$rrd_options .= " COMMENT:'                    Size      Free   % Used\\n'";

$hostname = gethostbyid($storage['device_id']);

$colour="CC0000";
$colour_area="ffaaaa";

$descr = substr(str_pad($storage[storage_descr], 12),0,12);
$descr = str_replace(":","\:",$descr);

$percentage  = round($storage['storage_perc'], 0);

$background = get_percentage_colours($percentage);

$rrd_options .= " DEF:$storage[storage_id]used=$rrd_filename:used:AVERAGE";
$rrd_options .= " DEF:$storage[storage_id]free=$rrd_filename:free:AVERAGE";
$rrd_options .= " CDEF:$storage[storage_id]size=$storage[storage_id]used,$storage[storage_id]free,+";
$rrd_options .= " CDEF:$storage[storage_id]perc=$storage[storage_id]used,$storage[storage_id]size,/,100,*";
$rrd_options .= " AREA:$storage[storage_id]perc#" . $background['right'] . ":";
$rrd_options .= " LINE1.25:$storage[storage_id]perc#" . $background['left'] . ":'$descr'";
$rrd_options .= " GPRINT:$storage[storage_id]size:LAST:%6.2lf%sB";
$rrd_options .= " GPRINT:$storage[storage_id]free:LAST:%6.2lf%sB";
$rrd_options .= " GPRINT:$storage[storage_id]perc:LAST:%5.2lf%%\\\\n";

?>
