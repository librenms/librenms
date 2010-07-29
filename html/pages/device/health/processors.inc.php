<?php

$graph_type = "processor_usage";

echo("<div style='margin-top: 5px; padding: 0px;'>");
echo("<table width=100% cellpadding=6 cellspacing=0>");
$i = '1';
$procs = mysql_query("SELECT * FROM `processors` WHERE device_id = '" . $device['device_id'] . "'");
while($proc = mysql_fetch_array($procs)) {

  $proc_url   = "device/".$device['device_id']."/health/processors/";

  $mini_url = "graph.php?id=".$proc['processor_id']."&type=".$graph_type."&from=".$day."&to=".$now."&width=80&height=20&bg=f4f4f4";

  $text_descr = $proc['processor_descr'];

  $text_descr = rewrite_entity_descr($text_descr);

  $proc_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname']." - ".$text_descr;
  $proc_popup .= "</div><img src=\'graph.php?id=" . $proc['processor_id'] . "&type=".$graph_type."&from=$month&to=$now&width=400&height=125\'>";
  $proc_popup .= "', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\"";

  $perc = round($proc['processor_usage']);

  if($perc > '90') { $left_background='c4323f'; $right_background='C96A73';
  } elseif($perc > '75') { $left_background='bf5d5b'; $right_background='d39392';
  } elseif($perc > '50') { $left_background='bf875b'; $right_background='d3ae92';
  } elseif($perc > '25') { $left_background='5b93bf'; $right_background='92b7d3';
  } else { $left_background='9abf5b'; $right_background='bbd392'; }

  echo("<tr bgcolor=$row_colour>
         <td class=tablehead><a href='".$proc_url."' $proc_popup>" . $text_descr . "</a></td>
         <td width=90><a href='".$proc_url."'  $proc_popup><img src='$mini_url'></a></td>
         <td width=200><a href='".$proc_url."' $proc_popup>
         ".print_percentage_bar (400, 20, $perc, $perc."%", "ffffff", $left_background, (100 - $perc)."%" , "ffffff", $right_background)."
          </a></td>
       </tr>");

  echo("<tr bgcolor='$row_colour'><td colspan=5>");

  $graph_array['id'] = $proc['processor_id'];
  $graph_array['type'] = $graph_type;

  include("includes/print-quadgraphs.inc.php");

}

echo("</table>");
echo("</div>");


?>
