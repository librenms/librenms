<?php

  if(!$graph_type) { $graph_type = $_GET['type']; }

  $daily_traffic   = "graph.php?if=" . $interface['interface_id'] . "&type=$graph_type&from=$day&to=$now&width=217&height=100";
  $daily_url       = "graph.php?if=" . $interface['interface_id'] . "&type=$graph_type&from=$day&to=$now&width=500&height=150";

  $weekly_traffic  = "graph.php?if=" . $interface['interface_id'] . "&type=$graph_type&from=$week&to=$now&width=217&height=100";
  $weekly_url      = "graph.php?if=" . $interface['interface_id'] . "&type=$graph_type&from=$week&to=$now&width=500&height=150";

  $monthly_traffic = "graph.php?if=" . $interface['interface_id'] . "&type=$graph_type&from=$month&to=$now&width=217&height=100";
  $monthly_url     = "graph.php?if=" . $interface['interface_id'] . "&type=$graph_type&from=$month&to=$now&width=500&height=150";

  $yearly_traffic  = "graph.php?if=" . $interface['interface_id'] . "&type=$graph_type&from=$year&to=$now&width=217&height=100";
  $yearly_url  = "graph.php?if=" . $interface['interface_id'] . "&type=$graph_type&from=$year&to=$now&width=500&height=150";

  echo("<a href='?page=interface&id=" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT, BORDER, 0);\" onmouseout=\"return nd();\">
        <img src='$daily_traffic' border=0></a> ");
  echo("<a href='?page=interface&id=" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT, BORDER, 0);\" onmouseout=\"return nd();\">
        <img src='$weekly_traffic' border=0></a> ");
  echo("<a href='?page=interface&id=" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT, BORDER, 0, WIDTH, 350);\" onmouseout=\"return nd();\">
        <img src='$monthly_traffic' border=0></a> ");
  echo("<a href='?page=interface&id=" . $interface['interface_id'] . "' onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT, BORDER, 0, WIDTH, 350);\" onmouseout=\"return nd();\">
        <img src='$yearly_traffic' border=0></a>");

?>
