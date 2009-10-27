<?php

include("common.inc.php");

$rrd_options .= " -l 0 -E ";

$iter = "1";
$sql = mysql_query("SELECT * FROM temperature where temp_host = '$device_id'");
$rrd_options .= " COMMENT:'                           Cur   Min   Max\\n'";
while($temperature = mysql_fetch_array($sql)) {
  if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
  } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
  } elseif($iter=="7") {$colour="FF0084"; unset($iter); }
  $temperature['temp_descr_fixed'] = str_pad($temperature['temp_descr'], 22);
  $temperature['temp_descr_fixed'] = substr($temperature['temp_descr_fixed'],0,22);
  $temprrd  = addslashes($config['rrd_dir'] . "/$hostname/temp-" . str_replace("/", "_", str_replace(" ", "_",$temperature['temp_descr'])) . ".rrd");
  $temprrd  = str_replace(")", "_", $temprrd);
  $temprrd  = str_replace("(", "_", $temprrd);
  $rrd_options .= " DEF:temp" . $temperature[temp_id] . "=$temprrd:temp:AVERAGE ";
  $rrd_options .= " LINE1:temp" . $temperature[temp_id] . "#" . $colour . ":'" . $temperature[temp_descr_fixed] . "' ";
  $rrd_options .= " GPRINT:temp" . $temperature[temp_id] . ":LAST:%3.0lfC ";
  $rrd_options .= " GPRINT:temp" . $temperature[temp_id] . ":MIN:%3.0lfC ";
  $rrd_options .= " GPRINT:temp" . $temperature[temp_id] . ":MAX:%3.0lfC\\\l ";
  $iter++;
}


?>
