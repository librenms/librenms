<?php

$graph_type = "mempool_usage";

echo("<div style='margin-top: 5px; padding: 0px;'>");
echo("<table width=100% cellpadding=6 cellspacing=0>");

echo("<tr class=tablehead>
        <th width=280>Device</th>
        <th>Memory</th>
        <th width=100></th>
        <th width=280>Usage</th>
        <th width=50>Used</th>
      </tr>");

$i = '1';
foreach (dbFetchRows("SELECT * FROM `mempools` AS M, `devices` as D WHERE D.device_id = M.device_id ORDER BY D.hostname") as $mempool)
{
  if (device_permitted($mempool['device_id']))
  {
    if (!is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $text_descr = $mempool['mempool_descr'];

    $mempool_url = "device/device=".$mempool['device_id']."/tab=health/metric=mempool/";
    $mini_url = "graph.php?id=".$mempool['mempool_id']."&amp;type=".$graph_type."&amp;from=".$day."&amp;to=".$now."&amp;width=80&amp;height=20&amp;bg=f4f4f4";

    $mempool_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$text_descr;
    $mempool_popup .= "</div><img src=\'graph.php?id=" . $mempool['mempool_id'] . "&amp;type=".$graph_type."&amp;from=$month&amp;to=$now&amp;width=400&amp;height=125\'>";
    $mempool_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

    $total = formatStorage($mempool['mempool_total']);
    $used = formatStorage($mempool['mempool_used']);
    $free = formatStorage($mempool['mempool_free']);

    $background = get_percentage_colours($mempool['mempool_perc']);

    echo("<tr bgcolor=$row_colour>
           <td>".generate_device_link($mempool)."</td>
           <td class=tablehead><a href='".$mempool_url."' $mempool_popup>" . $text_descr . "</a></td>
           <td width=90><a href='".$mempool_url."'  $mempool_popup><img src='$mini_url'></a></td>
           <td width=200><a href='".$mempool_url."' $mempool_popup>
           ".print_percentage_bar (400, 20, $mempool['mempool_perc'], "$used / $total", "ffffff", $background['left'], $free , "ffffff", $background['right'])."
            </a></td>
            <td width=50>".$mempool['mempool_perc']."%</td>
         </tr>");

    if ($vars['view'] == "graphs")
    {
      echo("<tr bgcolor='$row_colour'><td colspan=5>");

      $daily_graph   = "graph.php?id=" . $mempool['mempool_id'] . "&amp;type=".$graph_type."&amp;from=$day&amp;to=$now&amp;width=211&amp;height=100";
      $daily_url       = "graph.php?id=" . $mempool['mempool_id'] . "&amp;type=".$graph_type."&amp;from=$day&amp;to=$now&amp;width=400&amp;height=150";

      $weekly_graph  = "graph.php?id=" . $mempool['mempool_id'] . "&amp;type=".$graph_type."&amp;from=$week&amp;to=$now&amp;width=211&amp;height=100";
      $weekly_url      = "graph.php?id=" . $mempool['mempool_id'] . "&amp;type=".$graph_type."&amp;from=$week&amp;to=$now&amp;width=400&amp;height=150";

      $monthly_graph = "graph.php?id=" . $mempool['mempool_id'] . "&amp;type=".$graph_type."&amp;from=$month&amp;to=$now&amp;width=211&amp;height=100";
      $monthly_url     = "graph.php?id=" . $mempool['mempool_id'] . "&amp;type=".$graph_type."&amp;from=$month&amp;to=$now&amp;width=400&amp;height=150";

      $yearly_graph  = "graph.php?id=" . $mempool['mempool_id'] . "&amp;type=".$graph_type."&amp;from=$year&amp;to=$now&amp;width=211&amp;height=100";
      $yearly_url  = "graph.php?id=" . $mempool['mempool_id'] . "&amp;type=".$graph_type."&amp;from=$year&amp;to=$now&amp;width=400&amp;height=150";

      echo("<a onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$daily_graph' border=0></a> ");
      echo("<a onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$weekly_graph' border=0></a> ");
      echo("<a onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$monthly_graph' border=0></a> ");
      echo("<a onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$yearly_graph' border=0></a>");
      echo("</td></tr>");
    } # endif graphs

    $i++;
  }
}

echo("</table>");
echo("</div>");

?>
