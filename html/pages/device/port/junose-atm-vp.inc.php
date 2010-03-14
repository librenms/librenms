<?php

function print_graph ($args) {

  global $config;

  $sep = "?";
  $url = $config['base_url'] . "/graph.php";
  foreach ($args as $arg) {
    $url .= $sep.$arg;
  }

  return "<img src=\"".$url."\" border=0>";

}


  echo("<table cellspacing=0 cellpadding=5 border=0>");

  $vps = mysql_query("SELECT * FROM juniAtmVp WHERE interface_id = '".$interface['interface_id']."'");
  while($vp = mysql_fetch_array($vps)) {
    echo('<tr>');
    echo('<td>VP'.$vp['vp_id'].' '.$vp['vp_descr'].'</td>');
    echo('</tr>');

    $graph_array['height'] = "150";
    $graph_array['width']  = "150";
    $graph_array['to']     = $now;
    $graph_array['id']     = $vp['juniAtmVp_id'];
    $graph_array['type']   = "juniAtmVp_".$_GET['optc'];
    
    


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



  }

  echo("</table>");

?>
