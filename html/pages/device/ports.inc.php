<?php

if($_GET['opta'] == graphs ) {
  if($_GET['optb']) { $graph_type = "port_" . $_GET['optb']; } else { $graph_type = "port_bits"; }
}

print_optionbar_start();

echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/'>Basic</a> | 
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/details/'>Details</a> | 
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/arp/'>ARP Table</a> | 
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/adsl/'>ADSL</a> |
Graphs: ");

$graph_types = array("bits" => "Bits",
                     "upkts" => "Unicast Packets",
                     "nupkts" => "Non-Unicast Packets", 
                     "errors" => "Errors", 
                     "etherlike" => "Etherlike");

foreach ($graph_types as $type => $descr) {
  echo("$type_sep 
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/graphs/$type/'>$descr</a> 
  (<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/graphs/$type/thumbs/'>Mini</a>) ");
  $type_sep = " | ";
}


print_optionbar_end();

if($_GET['optc'] == thumbs) {

  $timeperiods = array('-1day','-1week','-1month','-1year');
  $from = '-1day';
  echo("<div style='display: block; clear: both; margin: auto;'>");
  $sql  = "select * from ports WHERE device_id = '".$device['device_id']."' ORDER BY ifIndex";
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
  if($_GET['opta'] == "arp" ) { 
    include("arp.inc.php");
  } elseif($_GET['opta'] == "adsl") {
    echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");

    echo("<tr><th>Port</th><th>Sync Speed</th><th>Attainable Speed</th><th>Attenuation</th><th>SNR Margin</th><th>Output Powers</th></tr>");
    $i = "0";
    $interface_query = mysql_query("select * from `ports` AS P, `ports_adsl` AS A WHERE P.device_id = '".$device['device_id']."' AND A.interface_id = P.interface_id AND P.deleted = '0' ORDER BY `ifIndex` ASC");
    while($interface = mysql_fetch_array($interface_query)) {
      include("includes/print-interface-adsl.inc.php");
      $i++;
    }
    echo("</table></div>");
    echo("<div style='min-height: 150px;'></div>");
  } else {
    if($_GET['opta'] == "details" ) { $port_details = 1; }
    echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
    $i = "1";
    $interface_query = mysql_query("select * from ports WHERE device_id = '".$device['device_id']."' AND deleted = '0' ORDER BY `ifIndex` ASC");
    while($interface = mysql_fetch_array($interface_query)) {
      include("includes/print-interface.inc.php");
      $i++; 
    }
    echo("</table></div>");
    echo("<div style='min-height: 150px;'></div>");
  }
}

?>
