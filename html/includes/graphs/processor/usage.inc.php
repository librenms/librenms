<?php

include("includes/graphs/common.inc.php");

$scale_min = "0";

$ds = "usage";

$descr = substr(str_pad(short_hrDeviceDescr($proc['processor_descr']), 28),0,28);
$descr = str_replace(":", "\:", $descr);

$colour_line = "cc0000";
$colour_minmax = "c5c5c5";

$graph_max = 1;
$unit_text = "Usage";

include("includes/graphs/generic_simplex.inc.php");

if($poop)
{

$scale_min = "0";
$scale_max = "100";

include("includes/graphs/common.inc.php");

$iter = "1";
$rrd_options .= " COMMENT:'                                 Cur   Max\\n'";

if ($iter=="1") { $colour="CC0000"; } elseif ($iter=="2") { $colour="008C00"; } elseif ($iter=="3") { $colour="4096EE"; }
elseif ($iter=="4") { $colour="73880A"; } elseif ($iter=="5") { $colour="D01F3C"; } elseif ($iter=="6") { $colour="36393D"; }
elseif ($iter=="7") { $colour="FF0084"; unset($iter); }

$descr = substr(str_pad(short_hrDeviceDescr($proc['processor_descr']), 28),0,28);
$descr = str_replace(":", "\:", $descr);

$rrd_options .= " DEF:proc" . $proc['hrDeviceIndex'] . "=".$rrd_filename.":usage:AVERAGE ";
$rrd_options .= " DEF:proc_max=".$rrd_filename.":usage:MAX";
$rrd_options .= " DEF:proc_min=".$rrd_filename.":usage:MIN";

$rrd_options .= " AREA:proc_max#c5c5c5";
$rrd_options .= " AREA:proc_min#ffffffff";

$rrd_options .= " LINE1:proc" . $proc['hrDeviceIndex'] . "#" . $colour . ":'" . $descr . "' ";
$rrd_options .= " GPRINT:proc" . $proc['hrDeviceIndex'] . ":LAST:%3.0lf%%";
$rrd_options .= " GPRINT:proc" . $proc['hrDeviceIndex'] . ":MAX:%3.0lf%%\\\l ";
$iter++;
}
?>
