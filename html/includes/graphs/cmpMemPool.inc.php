<?php

include("common.inc.php");

  $rrd_options .= " -u 100 -l 0 -E -b 1024 ";

  $iter = "1";
  $sql = mysql_query("SELECT * FROM `cmpMemPool` AS C, `devices` AS D where C.`cmp_id` = '".mres($_GET['id'])."' AND C.device_id = D.device_id");
  $rrd_options .= " COMMENT:\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ Cur\ \ \ \ Max\\\\n";
  while($mempool = mysql_fetch_array($sql)) {
    if($iter=="1") {$colour="CC0000";} elseif($iter=="2") {$colour="008C00";} elseif($iter=="3") {$colour="4096EE";
    } elseif($iter=="4") {$colour="73880A";} elseif($iter=="5") {$colour="D01F3C";} elseif($iter=="6") {$colour="36393D";
    } elseif($iter=="7") {$colour="FF0084"; unset($iter); }
    $mempool['descr_fixed'] = $mempool['cmpName'];
    $mempool['descr_fixed'] = str_pad($mempool['descr_fixed'], 28);
    $mempool['descr_fixed'] = substr($mempool['descr_fixed'],0,28);
    $oid = $mempool['Index'];
    $rrd  = $config['rrd_dir'] . "/".$mempool['hostname']."/cmp-$oid.rrd";
    $rrd_options .= " DEF:mempool" . $oid . "free=$rrd:free:AVERAGE ";
    $rrd_options .= " DEF:mempool" . $oid . "used=$rrd:used:AVERAGE ";
    $rrd_options .= " CDEF:mempool" . $oid . "total=mempool" . $oid . "used,mempool" . $oid . "used,mempool" . $oid . "free,+,/,100,* ";
    $rrd_options .= " LINE1:mempool" . $oid . "total#" . $colour . ":'" . $mempool['descr_fixed'] . "' ";
    $rrd_options .= " GPRINT:mempool" . $oid . "total:LAST:%3.0lf";
    $rrd_options .= " GPRINT:mempool" . $oid . "total:MAX:%3.0lf\\\l ";
    $iter++;
  }


?>
