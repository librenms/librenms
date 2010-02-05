<?php

include("common.inc.php");

  $rrd_options .= " -u 100 -l 0 -E -b 1024 ";

  $iter = "1";
  $sql = mysql_query("SELECT * FROM `cempMemPool` AS C, `devices` AS D where C.`cempMemPool_id` = '".mres($_GET['id'])."' AND C.device_id = D.device_id");
  $rrd_options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ Cur\ \ \ \ Max\\\\n";
  while($mempool = mysql_fetch_array($sql)) {
  $entPhysicalName = mysql_result(mysql_query("SELECT entPhysicalName from entPhysical WHERE device_id = '".$mempool['device_id']."'
                                               AND entPhysicalIndex = '".$mempool['entPhysicalIndex']."'"),0);
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); }
    $mempool['descr_fixed'] = $entPhysicalName . " " . $mempool['cempMemPoolName'];
    $mempool['descr_fixed'] = str_replace("Routing Processor", "RP", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_replace("Switching Processor", "SP", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_replace("Processor", "Proc", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_pad($mempool['descr_fixed'], 28);
    $mempool['descr_fixed'] = substr($mempool['descr_fixed'],0,28);
    $oid = $mempool['entPhysicalIndex'] . "." . $mempool['Index'];
    $rrd  = $config['rrd_dir'] . "/".$mempool['hostname']."/" . safename("cempMemPool-$oid.rrd");
    $id = $mempool['entPhysicalIndex'] . "-" . $mempool['Index'];
    $rrd_options .= " DEF:mempool" . $id . "free=$rrd:free:AVERAGE ";
    $rrd_options .= " DEF:mempool" . $id . "used=$rrd:used:AVERAGE ";
    $rrd_options .= " CDEF:mempool" . $id . "total=mempool" . $id . "used,mempool" . $id . "used,mempool" . $id . "free,+,/,100,* ";
    $rrd_options .= " LINE1:mempool" . $id . "total#" . $colour . ":'" . $mempool['descr_fixed'] . "' ";
    $rrd_options .= " GPRINT:mempool" . $id . "total:LAST:%3.0lf";
    $rrd_options .= " GPRINT:mempool" . $id . "total:MAX:%3.0lf\\\l ";
    $iter++;
  }


?>
