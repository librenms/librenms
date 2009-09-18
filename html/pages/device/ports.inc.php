<?php
echo("
<div style='margin:auto; text-align: center; margin-top: 0px; margin-bottom: 10px;'>
  <b class='rounded'>
  <b class='rounded1'><b></b></b>
  <b class='rounded2'><b></b></b>
  <b class='rounded3'></b>
  <b class='rounded4'></b>
  <b class='rounded5'></b></b>
  <div class='roundedfg' style='padding: 0px 5px;'>
  <div style='margin: auto; text-align: left; padding: 2px 5px; padding-left: 11px; clear: both; display:block; height:20px;'>

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
</div>
</div>
  <b class='rounded'>
  <b class='rounded5'></b>
  <b class='rounded4'></b>
  <b class='rounded3'></b>
  <b class='rounded2'><b></b></b>
  <b class='rounded1'><b></b></b></b>
</div>
");

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
  if($_GET['opta'] == "arp" ) { 

    $interface_query = mysql_query("select * from interfaces WHERE device_id = '$_GET[id]' AND deleted = '0' ORDER BY `ifIndex` ASC");
    echo("<table  border=0 cellspacing=0 cellpadding=3 width=800><tr><th>Address</th><th>Hardware Addr</th><th>Interface</th><th>Remote Device</th><th>Remote Port</th></tr>");
    $i = 1;
    while($interface = mysql_fetch_array($interface_query)) {
      $sql = "SELECT * FROM `ipv4_mac` WHERE `interface_id` = '".$interface['interface_id']."'";
      $arp_query = mysql_query($sql);
      while($arp = mysql_fetch_array($arp_query)) {       
        $i++;
        if(!is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }
	$r_sql = "SELECT * FROM `ipv4_addresses` AS A, `interfaces` AS I, `devices` AS D WHERE I.interface_id = A.interface_id AND
                I.device_id = D.device_id AND A.ipv4_address = '".$arp['ipv4_address']."' ORDER BY A.ipv4_address";
	$remote = mysql_fetch_array(mysql_query($r_sql));
        $mac = formatMac($arp['mac_address']);         
        echo("<tr style=\"background-color: $row_colour; padding: 5px;\" valign=top>
                <td>" . $arp['ipv4_address'] . "</td><td>" . $mac . "</td><td>".generateiflink($interface)."</td>");
        if ($remote['interface_id'] == $interface['interface_id']) {
          echo("<td><i>local</i></td><td><i>local</i></td>");
        } elseif($remote['device_id']) {
         echo("<td>" . generatedevicelink($remote) . "</td><td>" . generateiflink($remote) . "</td>");
        } else {
         echo("<td></td><td></td>");
        }
        echo("</tr>");
      }
      echo("</div>");
    }
    echo("</table>");
  } else {
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
}

?>
