<?php

$device = device_by_id_cache($id);
$query = mysql_query("SELECT * FROM `mempools` where `device_id` = '".$id."'");

include("common.inc.php");

  $rrd_options .= " -u 100 -l 0 -E -b 1024 ";

  $iter = "1"; $i=1;
  $rrd_options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ Cur\ \ \ \ Max\\\\n";
  while($mempool = mysql_fetch_array($query)) {
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); }
    $descr = substr(str_pad(short_hrDeviceDescr($mempool['mempool_descr']), 28),0,28);
    $descr = str_replace(":", "\:", $descr);
    $rrd  = $config['rrd_dir'] . "/".$device['hostname']."/" . safename("mempool-".$mempool['mempool_type']."-".$mempool['mempool_index'].".rrd");
    $rrd_options .= " DEF:mempoolfree$i=$rrd:free:AVERAGE ";
    $rrd_options .= " DEF:mempoolused$i=$rrd:used:AVERAGE ";
    $rrd_options .= " CDEF:mempooltotal$i=mempoolused$i,mempoolused$i,mempoolfree$i,+,/,100,* ";
    $rrd_options .= " LINE1:mempooltotal$i#" . $colour . ":'" . $descr . "' ";
    $rrd_options .= " GPRINT:mempooltotal$i:LAST:%3.0lf";
    $rrd_options .= " GPRINT:mempooltotal$i:MAX:%3.0lf\\\l ";
    $iter++; $i++;
  }

?>
