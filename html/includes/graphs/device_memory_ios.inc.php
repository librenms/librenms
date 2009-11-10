<?php

include("common.inc.php");

$rrd_options .= " -l 0 -E -b 1024 -u 100 -r";

if(mysql_result(mysql_query("SELECT COUNT(*) FROM `cempMemPool` WHERE `device_id` = '$device_id'"),0) > '0') {
  $iter = "1";
  $rrd_options .= " COMMENT:'                       Currently Used    Max\\n'";
  $sql = mysql_query("SELECT * FROM `cempMemPool` where `device_id` = '$device_id'");
  while($mempool = mysql_fetch_array($sql)) {
    $entPhysicalName = mysql_result(mysql_query("SELECT entPhysicalName from entPhysical WHERE device_id = '".$device_id."'
                                                 AND entPhysicalIndex = '".$mempool['entPhysicalIndex']."'"),0);
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); }
    $mempool['descr_fixed'] = $entPhysicalName . " " . $mempool['cempMemPoolName'];
    $mempool['descr_fixed'] = str_replace("Routing Processor", "RP", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_replace("Switching Processor", "SP", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_replace("Processor", "Proc", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_pad($mempool['descr_fixed'], 20);
    $mempool['descr_fixed'] = substr($mempool['descr_fixed'],0,20);
    $oid = $mempool['entPhysicalIndex'] . "." . $mempool['Index'];
    $rrd  = $config['rrd_dir'] . "/$hostname/cempMemPool-$oid.rrd";
    $rrd_options .= " DEF:mempool" . $iter . "free=$rrd:free:AVERAGE";
    $rrd_options .= " DEF:mempool" . $iter . "used=$rrd:used:AVERAGE";
    $rrd_options .= " DEF:mempool" . $iter . "free_m=$rrd:free:MAX";
    $rrd_options .= " DEF:mempool" . $iter . "used_m=$rrd:used:MAX";
    $rrd_options .= " CDEF:mempool" . $iter . "total=mempool" . $iter . "used,mempool" . $iter . "used,mempool" . $iter . "free,+,/,100,* ";
    
    $rrd_options .= " LINE1:mempool" . $iter . "total#" . $colour . ":'" . $mempool['descr_fixed'] . "' ";
    $rrd_options .= " GPRINT:mempool" . $iter . "used:LAST:%6.2lf%s";
    $rrd_options .= " GPRINT:mempool" . $iter . "total:LAST:%3.0lf%%";
    $rrd_options .= " GPRINT:mempool" . $iter . "total:MAX:%3.0lf%%\\\\n";
    $iter++;
  }
} else {
  $iter = "1";
  $rrd_options .= " COMMENT:'                       Currently Used    Max\\n'";
  $sql = mysql_query("SELECT * FROM `cmpMemPool` where `device_id` = '$device_id'");
  while($mempool = mysql_fetch_array($sql)) {
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); }
    $mempool['descr_fixed'] = $mempool['cmpName'];
    $mempool['descr_fixed'] = str_pad($mempool['descr_fixed'], 20);
    $mempool['descr_fixed'] = substr($mempool['descr_fixed'],0,20);
    $oid = $mempool['Index'];
    $rrd = $config['rrd_dir'] . "/$hostname/cmp-$oid.rrd";
    $rrd_options .= " DEF:mempool" . $iter . "free=$rrd:free:AVERAGE";
    $rrd_options .= " DEF:mempool" . $iter . "used=$rrd:used:AVERAGE";
    $rrd_options .= " DEF:mempool" . $iter . "free_m=$rrd:free:MAX";
    $rrd_options .= " DEF:mempool" . $iter . "used_m=$rrd:used:MAX";
    $rrd_options .= " CDEF:mempool" . $iter . "total=mempool" . $iter . "used,mempool" . $iter . "used,mempool" . $iter . "free,+,/,100,* ";
    $rrd_options .= " LINE1:mempool" . $iter . "total#" . $colour . ":'" . $mempool['descr_fixed'] . "' ";
    $rrd_options .= " GPRINT:mempool" . $iter . "used:LAST:%6.2lf%s";
    $rrd_options .= " GPRINT:mempool" . $iter . "total:LAST:%3.0lf%%";
    $rrd_options .= " GPRINT:mempool" . $iter . "total:MAX:%3.0lf%%\\\\n";
    $iter++;
  }
}

?>
