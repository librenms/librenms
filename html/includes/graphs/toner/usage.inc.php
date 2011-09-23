<?php
$scale_min = "0";
$scale_max = "100";

include("includes/graphs/common.inc.php");

$iter = "1";

$rrd_options .= " COMMENT:'                                 Cur   Max\\n'";

if ($iter=="1") { $colour="CC0000"; } elseif ($iter=="2") { $colour="008C00"; } elseif ($iter=="3") { $colour="4096EE"; }
elseif ($iter=="4") { $colour="73880A"; } elseif ($iter=="5") { $colour="D01F3C"; } elseif ($iter=="6") { $colour="36393D"; }
elseif ($iter=="7") { $colour="FF0084"; unset($iter); }

$descr = substr(str_pad($toner['toner_descr']),0,28);

if (stripos($toner['toner_descr'],"cyan"   ) !== false || substr($toner['toner_descr'],-1) == 'C') { $colour = "55D6D3"; }
if (stripos($toner['toner_descr'],"magenta") !== false || substr($toner['toner_descr'],-1) == 'M') { $colour = "F24AC8"; }
if (stripos($toner['toner_descr'],"yellow" ) !== false || substr($toner['toner_descr'],-1) == 'Y') { $colour = "FFF200"; }
if (stripos($toner['toner_descr'],"black"  ) !== false || substr($toner['toner_descr'],-1) == 'K') { $colour = "000000"; }

$background = get_percentage_colours(100-$toner['toner_current']);

$rrd_options .= " DEF:toner" . $toner['toner_id'] . "=".$rrd_filename.":toner:AVERAGE ";

$rrd_options .= " LINE1:toner" . $toner['toner_id'] . "#" . $colour . ":'" . $descr . "' ";

$rrd_options .= " AREA:toner" . $toner['toner_id' ] . "#" . $background['right'] . ":";
$rrd_options .= " GPRINT:toner" . $toner['toner_id'] . ":AVERAGE:'%5.0lf%%'";
$rrd_options .= " GPRINT:toner" . $toner['toner_id'] . ":MIN:'%5.0lf%%'";
$rrd_options .= " GPRINT:toner" . $toner['toner_id'] . ":MAX:%5.0lf%%\\\\l";

?>
