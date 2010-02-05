<?php

$scale_min = "0";

include("common.inc.php");

$rrd_options .= " -b 1024";

  $iter = "1";
  $sql = mysql_query("SELECT * FROM storage where storage_id = '".mres($_GET['id'])."'");
  $rrd_options .= " COMMENT:'                    Size       Used    %age\\n'";
  while($fs = mysql_fetch_array($sql)) {
    $hostname = gethostbyid($fs['host_id']);
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; $iter = "0"; }
    $descr = substr(str_pad($fs[hrStorageDescr], 12),0,12);
    $descr = str_replace(":","\:",$descr);
    $rrd = $config['rrd_dir'] . "/$hostname/" . safename("hrStorage-".$fs['hrStorageIndex'].".rrd");
    $rrd_options .= " DEF:$fs[storage_id]=$rrd:used:AVERAGE";
    $rrd_options .= " DEF:$fs[storage_id]s=$rrd:size:AVERAGE";
    $rrd_options .= " DEF:$fs[storage_id]p=$rrd:perc:AVERAGE";
    $rrd_options .= " LINE1.25:$fs[storage_id]p#" . $colour . ":'$descr'";
    $rrd_options .= " GPRINT:$fs[storage_id]s:LAST:%6.2lf%SB";
    $rrd_options .= " GPRINT:$fs[storage_id]:LAST:%6.2lf%SB";
    $rrd_options .= " GPRINT:$fs[storage_id]p:LAST:%5.2lf%%\\\\n";
    $iter++;
  }

?>
