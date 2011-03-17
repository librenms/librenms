<?php

$graph_type = "mempool_usage";

echo("<div style='margin-top: 5px; padding: 0px;'>");
echo("<table width=100% cellpadding=6 cellspacing=0>");

$i = '1';
$mempools = mysql_query("SELECT * FROM `mempools` WHERE device_id = '" . $device['device_id'] . "'");

while ($mempool = mysql_fetch_array($mempools))
{
  if (!is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $text_descr = rewrite_entity_descr($mempool['mempool_descr']);

  $mempool_url   = "device/".$device['device_id']."/health/memory/";
  $mini_url = "graph.php?id=".$mempool['mempool_id']."&type=".$graph_type."&from=".$day."&to=".$now."&width=80&height=20&bg=f4f4f4";

  $mempool_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$text_descr;
  $mempool_popup .= "</div><img src=\'graph.php?id=" . $mempool['mempool_id'] . "&type=".$graph_type."&from=$month&to=$now&width=400&height=125\'>";
  $mempool_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

  $total = formatStorage($mempool['mempool_total']);
  $used = formatStorage($mempool['mempool_used']);
  $free = formatStorage($mempool['mempool_free']);

  $perc = round($mempool['mempool_used'] / $mempool['mempool_total'] * 100);

  if ($perc > '90') { $left_background='c4323f'; $right_background='C96A73'; }
  elseif ($perc > '75') { $left_background='bf5d5b'; $right_background='d39392'; }
  elseif ($perc > '50') { $left_background='bf875b'; $right_background='d3ae92'; }
  elseif ($perc > '25') { $left_background='5b93bf'; $right_background='92b7d3'; }
  else { $left_background='9abf5b'; $right_background='bbd392'; }

  echo("<tr bgcolor=$row_colour><td class=tablehead><a href='".$mempool_url."' $mempool_popup>" . $text_descr . "</a></td>
           <td width=90><a href='".$mempool_url."'  $mempool_popup><img src='$mini_url'></a></td>
           <td width=200><a href='".$mempool_url."' $mempool_popup>
           ".print_percentage_bar (400, 20, $perc, "$used / $total", "ffffff", $left_background, $free , "ffffff", $right_background)."
            </a></td>
            <td width=50>".$perc."%</td>
         </tr>");

  echo("<tr bgcolor='$row_colour'><td colspan=5>");

  $graph_array['id'] = $mempool['mempool_id'];
  $graph_array['type'] = $graph_type;

  include("includes/print-quadgraphs.inc.php");

  echo("</td></tr>");

  $i++;
}

echo("</table>");
echo("</div>");

?>