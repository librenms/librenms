<?php

$graph_type = "mempool_usage";

if(mysql_result(mysql_query("SELECT count(*) from mempools WHERE device_id = '" . $device['device_id'] . "'"),0)) {
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p style='padding: 0px 5px 5px;' class=sectionhead><img align='absmiddle' src='".$config['base_url']."/images/icons/memory.png'> Memory Pools</p>");
  echo("<table width=100% cellspacing=0 cellpadding=5>");
  $mempool_rows = '1';
  $mempools = mysql_query("SELECT * FROM `mempools` WHERE device_id = '" . $device['device_id'] . "'");
  while($mempool = mysql_fetch_array($mempools)) {
    if(is_integer($mempool_rows/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }
    $perc = round($mempool['mempool_used'] / ($mempool['mempool_total']) * 100,2);

    $text_descr = rewrite_entity_descr($mempool['mempool_descr']);

    $mempool_url   = "/device/".$device['device_id']."/health/memory/";
    $mini_url = $config['base_url'] . "/graph.php?id=".$mempool['mempool_id']."&type=".$graph_type."&from=".$day."&to=".$now."&width=80&height=20&bg=f4f4f4";

    $mempool_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$text_descr;
    $mempool_popup .= "</div><img src=\'graph.php?id=" . $mempool['mempool_id'] . "&type=".$graph_type."&from=$month&to=$now&width=400&height=125\'>";
    $mempool_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

    $total = formatStorage($mempool['mempool_total']);
    $used = formatStorage($mempool['mempool_used']);
    $free = formatStorage($mempool['mempool_free']);

    if($perc > '90') { $left_background='c4323f'; $right_background='C96A73';
    } elseif($perc > '75') { $left_background='bf5d5b'; $right_background='d39392';
    } elseif($perc > '50') { $left_background='bf875b'; $right_background='d3ae92';
    } elseif($perc > '25') { $left_background='5b93bf'; $right_background='92b7d3';
    } else { $left_background='9abf5b'; $right_background='bbd392'; }

    echo("<tr bgcolor=$row_colour><td class=tablehead><a href='".$mempool_url."' $mempool_popup>" . $text_descr . "</a></td>
           <td width=90><a href='".$mempool_url."'  $mempool_popup><img src='$mini_url'></a></td>
           <td width=200><a href='".$mempool_url."' $mempool_popup>
           ".print_percentage_bar (200, 20, $perc, "$used / $total", "ffffff", $left_background, $perc . "%", "ffffff", $right_background)."  
           </a></td>
         </tr>");


    $mempool_rows++;
  }
  echo("</table>");
  echo("</div>");
}


?>
