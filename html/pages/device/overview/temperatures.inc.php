<?php

unset($temp_seperator);
if(mysql_result(mysql_query("SELECT count(temp_id) from temperature WHERE temp_host = '" . $device['device_id'] . "'"),0)) {
  $total = mysql_result(mysql_query("SELECT count(temp_id) from temperature WHERE temp_host = '" . $device['device_id'] . "'"),0);
  $rows = round($total / 2,0);
  echo("<div style='background-color: #eeeeee; margin: 5px; padding: 5px;'>");
  echo("<p class=sectionhead>Temperatures</p>");
  $i = '1';
  $temps = mysql_query("SELECT * FROM temperature WHERE temp_host = '" . $device['device_id'] . "'");
  echo("<table width=100% valign=top>");
  echo("<tr><td width=50%>");
  echo("<table width=100% cellspacing=0 cellpadding=2>");
  while($temp = mysql_fetch_array($temps)) {
    if(is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

    $temp_perc = $temp['temp_current'] / $temp['temp_limit'] * 100;

    $temp_colour = percent_colour($temp_perc);
    $temp_url  = "graph.php?id=" . $temp['temp_id'] . "&type=temp&from=$month&to=$now&width=400&height=125";
    $temp_link  = "<a href='/device/".$device['device_id']."/health/temp/' onmouseover=\"return ";
    $temp_link .= "overlib('<div class=list-large>".$device['hostname']." - ".$temp['temp_descr'];
    $temp_link .= "</div><img src=\'$temp_url\'>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";
    $temp_link .= $temp['temp_descr'] . "</a>";

    $temp['temp_descr'] = truncate($temp['temp_descr'], 25, '');
    echo("<tr bgcolor='$row_colour'><td><strong>$temp_link</strong></td><td width=40 class=tablehead><span style='color: $temp_colour'>" . $temp['temp_current'] . "&deg;C</span></td></tr>");
    if($i == $rows) { echo("</table></td><td valign=top><table width=100% cellspacing=0 cellpadding=2>"); }
    $i++;
  }
  echo("</table>");
  echo("</td></tr>");
  echo("</table>");
  echo("</div>");
}


?>
