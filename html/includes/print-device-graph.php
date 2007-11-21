<?php

  $daily_traffic   = "graph.php?host=" . $device['device_id'] . "&type=$graph_type&from=$day&to=$now&width=215&height=100";
  $daily_url       = "graph.php?host=" . $device['device_id'] . "&type=$graph_type&from=$day&to=$now&width=400&height=150";

  $weekly_traffic  = "graph.php?host=" . $device['device_id'] . "&type=$graph_type&from=$week&to=$now&width=215&height=100";
  $weekly_url      = "graph.php?host=" . $device['device_id'] . "&type=$graph_type&from=$week&to=$now&width=400&height=150";

  $monthly_traffic = "graph.php?host=" . $device['device_id'] . "&type=$graph_type&from=$month&to=$now&width=215&height=100";
  $monthly_url     = "graph.php?host=" . $device['device_id'] . "&type=$graph_type&from=$month&to=$now&width=400&height=150";

  $yearly_traffic  = "graph.php?host=" . $device['device_id'] . "&type=$graph_type&from=$year&to=$now&width=215&height=100";
  $yearly_url  = "graph.php?host=" . $device['device_id'] . "&type=$graph_type&from=$year&to=$now&width=400&height=150";

  echo("<a onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
        <img src='$daily_traffic' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
        <img src='$weekly_traffic' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
        <img src='$monthly_traffic' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
        <img src='$yearly_traffic' border=0></a>");

?>

