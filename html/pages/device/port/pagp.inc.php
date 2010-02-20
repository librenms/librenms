<?php

  global $config;

  if(!$graph_type) { $graph_type = "pagp_bits"; }

  $daily_traffic   = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$day&to=$now&width=215&height=100";
  $daily_url       = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$day&to=$now&width=500&height=150";

  $weekly_traffic  = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$week&to=$now&width=215&height=100";
  $weekly_url      = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$week&to=$now&width=500&height=150";

  $monthly_traffic = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$month&to=$now&width=215&height=100";
  $monthly_url     = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$month&to=$now&width=500&height=150";

  $yearly_traffic  = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$year&to=$now&width=215&height=100";
  $yearly_url      = $config['base_url'] . "/graph.php?port=" . $interface['interface_id'] . "&type=$graph_type&from=$year&to=$now&width=500&height=150";

  echo("<a href='/device/".$device['device_id']."/interface/".$interface['interface_id']."/' onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
        <img src='$daily_traffic' border=0></a> ");
  echo("<a href='/device/".$device['device_id']."/interface/".$interface['interface_id']."/' onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
        <img src='$weekly_traffic' border=0></a> ");
  echo("<a href='/device/".$device['device_id']."/interface/".$interface['interface_id']."/' onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT".$config['overlib_defaults'].", WIDTH, 350);\" onmouseout=\"return nd();\">
        <img src='$monthly_traffic' border=0></a> ");
  echo("<a href='/device/".$device['device_id']."/interface/".$interface['interface_id']."/' onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT".$config['overlib_defaults'].", WIDTH, 350);\" onmouseout=\"return nd();\">
        <img src='$yearly_traffic' border=0></a>");



  $members = mysql_query("SELECT * FROM `ports` WHERE `pagpGroupIfIndex` = '".$interface['ifIndex']."' and `device_id` = '".$device['device_id']."'");
  while($member = mysql_fetch_array($members)) {
    echo("$br<img src='images/16/brick_link.png' align=absmiddle> <strong>" . generateiflink($member) . " (PAgP)</strong>");
    $br = "<br />";
  }


?>
