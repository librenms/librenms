<?php

  $daily_traffic   = "graph.php?if=$iid&type=$graph_type&from=$day&to=$now&width=217&height=100";
  $daily_url       = "graph.php?if=$iid&type=$graph_type&from=$day&to=$now&width=550&height=175";

  $weekly_traffic  = "graph.php?if=$iid&type=$graph_type&from=$week&to=$now&width=217&height=100";
  $weekly_url      = "graph.php?if=$iid&type=$graph_type&from=$week&to=$now&width=550&height=175";

  $monthly_traffic = "graph.php?if=$iid&type=$graph_type&from=$month&to=$now&width=217&height=100";
  $monthly_url     = "graph.php?if=$iid&type=$graph_type&from=$month&to=$now&width=550&height=175";

  $yearly_traffic  = "graph.php?if=$iid&type=$graph_type&from=$year&to=$now&width=217&height=100";
  $yearly_url  = "graph.php?if=$iid&type=$graph_type&from=$year&to=$now&width=550&height=175";

  echo("<a href='?page=interface&id=$iid' onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$daily_traffic' border=0></a> ");
  echo("<a href='?page=interface&id=$iid' onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$weekly_traffic' border=0></a> ");
  echo("<a href='?page=interface&id=$iid' onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$monthly_traffic' border=0></a> ");
  echo("<a href='?page=interface&id=$iid' onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$yearly_traffic' border=0></a>");

?>
