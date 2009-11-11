<?php

$scale_min = "0";
$scale_max = "100";

include("common.inc.php");

  $iter = "1";

  $sql = mysql_query("SELECT * FROM `hrDevice` AS C, `devices` AS D where C.`hrDevice_id` = '".mres($_GET['id'])."' AND C.device_id = D.device_id");
  $rrd_options .= " COMMENT:'                                Cur    Max\\n'";
  while($proc = mysql_fetch_array($sql)) {
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); }
    $proc['descr_fixed'] = substr(str_pad(short_hrDeviceDescr($proc['hrDeviceDescr']), 28),0,28);
    $proc['descr_fixed'] = str_replace(":", "\:", $proc['descr_fixed']);
    $rrd  = $config['rrd_dir'] . "/".$proc['hostname']."/hrProcessor-" . $proc['hrDeviceIndex'] . ".rrd";
    $rrd_options .= " DEF:proc" . $proc['hrDeviceIndex'] . "=$rrd:usage:AVERAGE ";
    $rrd_options .= " LINE1:proc" . $proc['hrDeviceIndex'] . "#" . $colour . ":'" . $proc['descr_fixed'] . "' ";
    $rrd_options .= " GPRINT:proc" . $proc['hrDeviceIndex'] . ":LAST:%3.0lf";
    $rrd_options .= " GPRINT:proc" . $proc['hrDeviceIndex'] . ":MAX:%3.0lf\\\l ";
    $iter++;
  }


?>
