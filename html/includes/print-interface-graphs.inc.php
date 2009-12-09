<?php

  global $config;

  if(!$graph_type) { $graph_type = $_GET['type']; }

  if(!$device) { $device['device_id'] = getifhost($interface['interface_id']); }

  $daily_traffic   = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$day&to=$now&width=215&height=100";
  $daily_url       = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$day&to=$now&width=500&height=150";

  $weekly_traffic  = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$week&to=$now&width=215&height=100";
  $weekly_url      = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$week&to=$now&width=500&height=150";

  $monthly_traffic = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$month&to=$now&width=215&height=100";
  $monthly_url     = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$month&to=$now&width=500&height=150";

  $yearly_traffic  = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$year&to=$now&width=215&height=100";
  $yearly_url      = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$year&to=$now&width=500&height=150";

  echo("<a href='".$daily_url."' onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
        <img src='$daily_traffic' border=0></a> ");
  echo("<a href='".$weekly_url."' onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
        <img src='$weekly_traffic' border=0></a> ");
  echo("<a href='".$monthly_url."' onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT".$config['overlib_defaults'].", WIDTH, 350);\" onmouseout=\"return nd();\">
        <img src='$monthly_traffic' border=0></a> ");
  echo("<a href='".$yearly_url."' onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT".$config['overlib_defaults'].", WIDTH, 350);\" onmouseout=\"return nd();\">
        <img src='$yearly_traffic' border=0></a>");

?>
