<?php

$graph_type = "mempool_usage";

if (mysql_result(mysql_query("SELECT count(*) from mempools WHERE device_id = '" . $device['device_id'] . "'"),0))
{
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p style='padding: 0px 5px 5px;' class=sectionhead>");
  echo('<a class="sectionhead" href="device/'.$device['device_id'].'/health/mempool/">');
  echo("<img align='absmiddle' src='".$config['base_url']."/images/icons/memory.png'> Memory Pools</a></p>");
  echo("<table width=100% cellspacing=0 cellpadding=5>");
  $mempool_rows = '0';
  $mempools = mysql_query("SELECT * FROM `mempools` WHERE device_id = '" . $device['device_id'] . "'");

  while ($mempool = mysql_fetch_assoc($mempools))
  {
    if (is_integer($mempool_rows/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }
    $perc = round($mempool['mempool_perc'],0);

    $text_descr = rewrite_entity_descr($mempool['mempool_descr']);

    $mempool_url   = $config['base_url'] . "/graphs/".$mempool['mempool_id']."/mempool_usage/";
    $mini_url = $config['base_url'] . "/graph.php?id=".$mempool['mempool_id']."&amp;type=".$graph_type."&amp;from=".$day."&amp;to=".$now."&amp;width=80&amp;height=20&amp;bg=f4f4f4";

    $mempool_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$text_descr;
    $mempool_popup .= "</div><img src=\'graph.php?id=" . $mempool['mempool_id'] . "&amp;type=".$graph_type."&amp;from=$month&amp;to=$now&amp;width=400&amp;height=125\'>";
    $mempool_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

    $total = formatStorage($mempool['mempool_total']);
    $used = formatStorage($mempool['mempool_used']);
    $free = formatStorage($mempool['mempool_free']);

    $background = get_percentage_colours($perc);

    echo("<tr bgcolor=$row_colour><td class=tablehead><a href='".$mempool_url."' $mempool_popup>" . $text_descr . "</a></td>
           <td width=90><a href='".$mempool_url."'  $mempool_popup><img src='$mini_url'></a></td>
           <td width=200><a href='".$mempool_url."' $mempool_popup>
           ".print_percentage_bar (200, 20, $perc, "$used / $total", "ffffff", $background['left'], $perc . "%", "ffffff", $background['right'])."
           </a></td>
         </tr>");

    $mempool_rows++;
  }

  echo("</table>");
  echo("</div>");
}

?>
