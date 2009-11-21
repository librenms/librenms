<?php

if(mysql_result(mysql_query("SELECT count(*) from cmpMemPool WHERE device_id = '" . $device['device_id'] . "'"),0) && !$cemp) {
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p class=sectionhead>Memory Pools</p>");
  echo("<table width=100%>");
  $i = '1';
  $mempools = mysql_query("SELECT * FROM `cmpMemPool` WHERE device_id = '" . $device['device_id'] . "'");
  while($mempool = mysql_fetch_array($mempools)) {
    $perc = round($mempool['cmpUsed'] / ($mempool['cmpUsed'] + $mempool['cmpFree']) * 100,2);
    $mempool['descr_fixed'] = $mempool['cmpName'];
    $mempool['descr_fixed'] = str_replace("Routing Processor", "RP", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_replace("Switching Processor", "SP", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_replace("Processor", "Proc", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_replace("Sub-Module", "Mod", $mempool['descr_fixed']);
    $mempool['descr_fixed'] = str_replace("DFC Card", "DFC", $mempool['descr_fixed']);

    $proc_url   = "/device/".$device['device_id']."/health/cmp/";

    $mempool_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$mempool['descr_fixed'];
    $mempool_popup .= "</div><img src=\'graph.php?id=" . $mempool['cmp_id'] . "&type=cmpMemPool&from=$month&to=$now&width=400&height=125\'>";
    $mempool_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";


    if($mempool['cpuCPMTotal5minRev'] > '60') { $mempool_colour='#cc0000'; } else { $mempool_colour='#0000cc';  }
    echo("<tr><td class=tablehead><a href='$proc_url' $mempool_popup>" . $mempool['descr_fixed'] ."</a></td>
            <td><a href='#' $mempool_popup><img src='percentage.php?per=" . $perc . "'></a></td>
            <td style='font-weight: bold; color: $drv_colour'>$perc%</td>
            <td style='color: $drv_colour'>" . formatstorage($mempool['cmpFree'], 0) . "/" . formatstorage($mempool['cmpUsed'] + $mempool['cmpFree'], 0) . "</strong></td>
          </tr>");
    $i++;
  }
  echo("</table>");
  echo("</div>");
}


?>
