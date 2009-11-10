<?php

  echo("<div style='margin-top: 5px; padding: 0px;'>");
  echo("<table width=100% cellpadding=6 cellspacing=0>");
  $i = '1';
  $mempools = mysql_query("SELECT * FROM `cmpMemPool` WHERE device_id = '" . $device['device_id'] . "'");
  while($mempool = mysql_fetch_array($mempools)) {
    if(!is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $perc = round($mempool['cmpUsed'] / ($mempool['cmpUsed'] + $mempool['cmpFree']) * 100,2);

    $proc_url   = "?page=device/".$device['device_id']."/sensors/mempools/";

    $mempool_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$mempool['cmpName'];
    $mempool_popup .= "</div><img src=\'graph.php?id=" . $mempool['cmp_id'] . "&type=cmpMemPool&from=$month&to=$now&width=400&height=125\'>";
    $mempool_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

    if($mempool['cpuCPMTotal5minRev'] > '60') { $mempool_colour='#cc0000'; } else { $mempool_colour='#0000cc';  }
    echo("<tr bgcolor='$row_colour'><td width=350 class=tablehead><a href='' $mempool_popup>" . $mempool['cmpName'] ."</a></td>
            <td><a href='#' $mempool_popup><img src='percentage.php?per=" . $perc . "&width=600'></a></td>
            <td style='font-weight: bold; color: $drv_colour'>$perc%</td>
            <td style='color: $drv_colour'>" . formatstorage($mempool['cmpFree'], 0) . "/" . formatstorage($mempool['cmpUsed'] + $mempool['cmpFree'], 0) . "</strong></td>
          </tr>");
 
  echo("<tr bgcolor='$row_colour'><td colspan=5>");

  $daily_graph   = "graph.php?id=" . $mempool['cmp_id'] . "&type=cmpMemPool&from=$day&to=$now&width=211&height=100";
  $daily_url       = "graph.php?id=" . $mempool['cmp_id'] . "&type=cmpMemPool&from=$day&to=$now&width=400&height=150";

  $weekly_graph  = "graph.php?id=" . $mempool['cmp_id'] . "&type=cmpMemPool&from=$week&to=$now&width=211&height=100";
  $weekly_url      = "graph.php?id=" . $mempool['cmp_id'] . "&type=cmpMemPool&from=$week&to=$now&width=400&height=150";

  $monthly_graph = "graph.php?id=" . $mempool['cmp_id'] . "&type=cmpMemPool&from=$month&to=$now&width=211&height=100";
  $monthly_url     = "graph.php?id=" . $mempool['cmp_id'] . "&type=cmpMemPool&from=$month&to=$now&width=400&height=150";

  $yearly_graph  = "graph.php?id=" . $mempool['cmp_id'] . "&type=cmpMemPool&from=$year&to=$now&width=211&height=100";
  $yearly_url  = "graph.php?id=" . $mempool['cmp_id'] . "&type=cmpMemPool&from=$year&to=$now&width=400&height=150";

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
