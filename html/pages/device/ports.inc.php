<?php
echo("
<div style='background-color: ".$list_colour_b."; margin: auto; margin-bottom: 5px; text-align: left; padding: 7px; padding-left: 11px; clear: both; display:block; height:20px;'>
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/'>Basic</a> | 
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/details/'>Details</a> | Graphs:
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/graphs/bits/'>Bits</a> 
(<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/graphs/bits/thumbs/'>Mini</a>) | 
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/graphs/pkts/'>Packets</a> 
(<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/graphs/pkts/thumbs/'>Mini</a>) | 
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/graphs/nupkts/'>NU Packets</a>
(<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/graphs/nupkts/thumbs/'>Mini</a>) |
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/graphs/errors/'>Errors</a>
(<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/graphs/errors/thumbs/'>Mini</a>)</a>
</div> ");

if($_GET['opta'] == graphs ) {
  if($_GET['optb']) { $graph_type = $_GET['optb']; } else { $graph_type = "bits"; }
  $dographs = 1;
}

if($_GET['optc'] == thumbs) {

  $timeperiods = array('-1day','-1week','-1month','-1year');
  $from = '-1day';
  echo("<div style='display: block; clear: both; margin: auto;'>");
  $sql  = "select * from interfaces WHERE device_id = '".$device['device_id']."' ORDER BY ifIndex";
  $query = mysql_query($sql);
  unset ($seperator);
  while($interface = mysql_fetch_array($query)) {
    echo("<div style='display: block; padding: 3px; margin: 3px; min-width: 183px; max-width:183px; min-height:90px; max-height:90px; text-align: center; float: left; background-color: #e9e9e9;'>
    <div style='font-weight: bold;'>".makeshortif($interface['ifDescr'])."</div>
    <a href='device/".$device['device_id']."/interface/".$interface['interface_id']."/' onmouseover=\"return overlib('\
    <div style=\'font-size: 16px; padding:5px; font-weight: bold; color: #e5e5e5;\'>".$device['hostname']." - ".$interface['ifDescr']."</div>\
    ".$interface['ifAlias']." \
    <img src=\'graph.php?type=$graph_type&if=".$interface['interface_id']."&from=".$from."&to=".$now."&width=450&height=150\'>\
    ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<img src='graph.php?type=$graph_type&if=".$interface['interface_id']."&from=".$from."&to=".$now."&width=180&height=45&legend=no'>
    </a>
    <div style='font-size: 9px;'>".truncate(short_port_descr($interface['ifAlias']), 32, '')."</div>
    </div>");
  }
  echo("</div>");
} else {
  if($_GET['opta'] == "details" ) { $port_details = 1; }
  $hostname = gethostbyid($device['device_id']);
  echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
  $i = "1";
  $interface_query = mysql_query("select * from interfaces WHERE device_id = '$_GET[id]' AND deleted = '0' ORDER BY `ifIndex` ASC");
  while($interface = mysql_fetch_array($interface_query)) {
    include("includes/print-interface.inc");
    $i++; 
  }
  echo("</table></div>");
  echo("<div style='min-height: 150px;'></div>");
}

?>
