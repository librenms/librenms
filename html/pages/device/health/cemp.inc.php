<?php

  echo("<div style='margin-top: 5px; padding: 0px;'>");
  echo("<table width=100% cellpadding=6 cellspacing=0>");
  $i = '1';
  $mempools = mysql_query("SELECT * FROM `cempMemPool` WHERE device_id = '" . $device['device_id'] . "'");
  while($mempool = mysql_fetch_array($mempools)) {
    if(!is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }
    $entPhysicalName = mysql_result(mysql_query("SELECT entPhysicalName from entPhysical WHERE device_id = '".$device['device_id']."'
                                               AND entPhysicalIndex = '".$mempool['entPhysicalIndex']."'"),0);
    $perc = round($mempool['cempMemPoolUsed'] / ($mempool['cempMemPoolUsed'] + $mempool['cempMemPoolFree']) * 100,2);
    $mempool['descr_fixed'] = $entPhysicalName . " ". $mempool['cempMemPoolName'];
#    $mempool['descr_fixed'] = str_replace("Routing Processor", "RP", $mempool['descr_fixed']);
#    $mempool['descr_fixed'] = str_replace("Switching Processor", "SP", $mempool['descr_fixed']);
#    $mempool['descr_fixed'] = str_replace("Processor", "Proc", $mempool['descr_fixed']);

    $proc_url   = "?page=device/".$device['device_id']."/sensors/mempools/";

    $mempool_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$mempool['descr_fixed'];
    $mempool_popup .= "</div><img src=\'graph.php?id=" . $mempool['cempMemPool_id'] . "&type=cempMemPool&from=$month&to=$now&width=400&height=125\'>";
    $mempool_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";


    if($mempool['cpuCPMTotal5minRev'] > '60') { $mempool_colour='#cc0000'; } else { $mempool_colour='#0000cc';  }
    echo("<tr bgcolor='$row_colour'><td width=350 class=tablehead><a href='' $mempool_popup>" . $mempool['descr_fixed'] ."</a></td>
            <td><a href='#' $mempool_popup><img src='percentage.php?per=" . $perc . "&width=600'></a></td>
            <td style='font-weight: bold; color: $drv_colour'>$perc%</td>
            <td style='color: $drv_colour'>" . formatstorage($mempool['cempMemPoolFree'], 0) . "/" . formatstorage($mempool['cempMemPoolUsed'] + $mempool['cempMemPoolFree'], 0) . "</strong></td>
          </tr>");
 
  echo("<tr bgcolor='$row_colour'><td colspan=5>");

  $daily_graph   = "graph.php?id=" . $mempool['cempMemPool_id'] . "&type=cempMemPool&from=$day&to=$now&width=211&height=100";
  $daily_url       = "graph.php?id=" . $mempool['cempMemPool_id'] . "&type=cempMemPool&from=$day&to=$now&width=400&height=150";

  $weekly_graph  = "graph.php?id=" . $mempool['cempMemPool_id'] . "&type=cempMemPool&from=$week&to=$now&width=211&height=100";
  $weekly_url      = "graph.php?id=" . $mempool['cempMemPool_id'] . "&type=cempMemPool&from=$week&to=$now&width=400&height=150";

  $monthly_graph = "graph.php?id=" . $mempool['cempMemPool_id'] . "&type=cempMemPool&from=$month&to=$now&width=211&height=100";
  $monthly_url     = "graph.php?id=" . $mempool['cempMemPool_id'] . "&type=cempMemPool&from=$month&to=$now&width=400&height=150";

  $yearly_graph  = "graph.php?id=" . $mempool['cempMemPool_id'] . "&type=cempMemPool&from=$year&to=$now&width=211&height=100";
  $yearly_url  = "graph.php?id=" . $mempool['cempMemPool_id'] . "&type=cempMemPool&from=$year&to=$now&width=400&height=150";

  echo("<a onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$daily_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$weekly_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$monthly_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$yearly_graph' border=0></a>");
  echo("</td></tr>");


    $i++;
  }
  echo("</table>");
  echo("</div>");


?>
