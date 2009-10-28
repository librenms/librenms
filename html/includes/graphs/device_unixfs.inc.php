<?php

include("common.inc.php");

  $iter = "1";
  $sql = mysql_query("SELECT * FROM storage where host_id = '$device_id'");
  $rrd_options .= " COMMENT:'                      Size      Used    %age\\l'";
  while($fs = mysql_fetch_array($sql)) {
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; $iter = "0"; }

    $descr = str_pad($fs[hrStorageDescr], 14);
    $descr = substr($descr,0,14);
    $text = str_replace("/", "_", $fs['hrStorageDescr']);
    $rrd = $config['rrd_dir'] . "/$hostname/storage-$text.rrd";
    $rrd_options .= " DEF:$fs[storage_id]=$rrd:used:AVERAGE";
    $rrd_options .= " DEF:$fs[storage_id]s=$rrd:size:AVERAGE";
    $rrd_options .= " DEF:$fs[storage_id]p=$rrd:perc:AVERAGE";
    $rrd_options .= " LINE1.25:$fs[storage_id]p#" . $colour . ":'$descr'";
    $rrd_options .= " GPRINT:$fs[storage_id]s:LAST:%6.2lf%SB";
    $rrd_options .= " GPRINT:$fs[storage_id]:LAST:%6.2lf%SB";
    $rrd_options .= " GPRINT:$fs[storage_id]p:LAST:%5.2lf%%\\\\l";
    $iter++;
  }


?>
