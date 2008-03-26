
<table border=0 cellpadding=10 cellspacing=10 width=100%>
  <tr>
    <td bgcolor=#e5e5e5 valign=top>
<?php
#      <table width=100% border=0><tr><td><div style="margin-bottom: 5px; font-size: 18px; font-weight: bold;">Devices with Alerts</div></td><td width=35 align=center><div class=tablehead>Host</div></td><td align=center width=35><div class=tablehead>Int</div></td><td align=center width=35><div class=tablehead>Srv</div></tr>
?>
<?php

$nodes = array();

$sql = mysql_query("SELECT * FROM `devices` AS D, `devices_attribs` AS A WHERE D.status = '1' AND A.device_id = D.device_id AND A.attrib_type = 'uptime' AND A.attrib_value > '0' AND A.attrib_value < '86400'");

while($device = mysql_fetch_array($sql)){
  unset($already);
  $i = 0;
  while ($i <= count($nodes)) {
    $thisnode = $device['device_id'];
    if ($nodes[$i] == $thisnode) {
     $already = "yes";
    }
    $i++;
  }
  if(!$already) { $nodes[] = $device['device_id']; }
}


$sql = mysql_query("SELECT * FROM `devices` WHERE `status` = '0' AND `ignore` = '0'");
while($device = mysql_fetch_array($sql)){

      echo("<div style='border: solid 2px #d0D0D0; float: left; padding: 5px; width: 120px; height: 90px; background: #ffbbbb; margin: 4px;'>
      <center><strong>".generatedevicelink($device, shorthost($device['hostname']))."</strong><br />
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Device Down</span> 
      <span class=body-date-1>".truncate($device['location'], 20)."</span>
      </center></div>");

}

$sql = mysql_query("SELECT * FROM `interfaces` AS I, `devices` AS D WHERE I.device_id = D.device_id AND ifOperStatus = 'down' AND ifAdminStatus = 'up' AND D.ignore = '0' AND I.ignore = '0'");
while($interface = mysql_fetch_array($sql)){

      echo("<div style='border: solid 2px #D0D0D0; float: left; padding: 5px; width: 120px; height: 90px; background: #ffddaa; margin: 4px;'>
      <center><strong>".generatedevicelink($interface, shorthost($interface['hostname']))."</strong><br />
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Port Down</span> 
      <strong>".generateiflink($interface, makeshortif($interface['ifDescr']))."</strong> <br />
      <span class=body-date-1>".truncate($interface['ifAlias'], 20)."</span>
      </center></div>");

}

$sql = mysql_query("SELECT * FROM `services` AS S, `devices` AS D WHERE S.service_host = D.device_id AND service_status = 'down' AND D.ignore = '0' AND S.service_ignore = '0'");
while($service = mysql_fetch_array($sql)){

      echo("<div style='border: solid 2px #D0D0D0; float: left; padding: 5px; width: 120px; height: 90px; background: #ffddaa; margin: 4px;'>
      <center><strong>".generatedevicelink($service, shorthost($service['hostname']))."</strong><br />
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Service Down</span> 
      <strong>".$service['service_type']."</strong><br />
      <span class=body-date-1>".truncate($interface['ifAlias'], 20)."</span>
      </center></div>");

}

$sql = mysql_query("SELECT * FROM `devices` AS D, bgpPeers AS B WHERE bgpPeerState != 'established' AND B.device_id = D.device_id");
while($peer = mysql_fetch_array($sql)){

      echo("<div style='border: solid 2px #d0D0D0; float: left; padding: 5px; width: 120px; height: 90px; background: #ffddaa; margin: 4px;'>
      <center><strong>".generatedevicelink($peer, shorthost($peer['hostname']))."</strong><br />
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>BGP Down</span> 
      <strong>".$peer['bgpPeerIdentifier']."</strong> <br />
      <span class=body-date-1>AS".$peer['bgpPeerRemoteAs']." ".truncate($peer['astext'], 10)."</span>
      </center></div>");

}



foreach($nodes as $node) {

  unset($srvpop);

  $host = gethostbyid($node);

  $ints = mysql_result(mysql_query("SELECT count(*) FROM `interfaces` WHERE `ifOperStatus` = 'down' AND `ifAdminStatus` = 'up' AND `device_id` = '$node'"),0);
  $services = mysql_result(mysql_query("SELECT count(service_id) FROM `services` WHERE `service_status` = '0' AND `service_host` = '$node'"),0);

  $intlist = array();
  $sql = mysql_query("SELECT `ifDescr`, `ifAlias` FROM interfaces WHERE `ifOperStatus` = 'down' AND `ifAdminStatus` = 'up' AND `device_id` = '$node'");

  $uptime = mysql_result(mysql_query("SELECT attrib_value FROM `devices` AS D, `devices_attribs` AS A WHERE D.device_id = '$node' AND D.status = '1' AND A.device_id = D.device_id AND A.attrib_type = 'uptime'"),0);

  if($uptime < "86000") { $rebooted = 1; } else { $rebooted = 0; }

  while($int = mysql_fetch_row($sql)) { $intlist[] = "<b>$int[0]</b> - $int[1]"; } 
  foreach ($intlist as $intname) { $intpop .= "$br $intname"; $br = "<br />"; }
  unset($br);
  if($intpop) {$intpop = "onmouseover=\"return overlib('$intpop', WIDTH, 350);\" onmouseout=\"return nd();\""; }

  $srvlist = array();
  $sql = mysql_query("SELECT `service_type`, `service_message` FROM services WHERE `service_status` = '0' AND `service_host` = '$node'");
  while($srv = mysql_fetch_row($sql)) { $srvlist[] = "<b>$srv[0]</b> - " . trim($srv[1]); }
  foreach ($srvlist as $srvname) { $srvpop .= "$br " . truncate($srvname, 100); $br = "<br />"; }
  unset($br);
  if($srvpop) {
    $srvpop = "onmouseover=\"return overlib('$srvpop', WIDTH, 350);\" onmouseout=\"return nd();\"";
    $srvpop = str_replace("\n", ". ", $srvpop);
  }

  $mouseover = "onmouseover=\"return overlib('<img src=\'graph.php?host=$node&from=$week&to=$now&width=400&height=120&type=cpu\'>');\"
                onmouseout=\"return nd();\"";

  if(hoststatus($node)) { 
    $statimg = "<img align=absmiddle src=images/16/lightbulb.png alt='Host Up'>"; 
    $background_image = "images/boxbgorange.png"; 
    $background_color = "#ddffdd";
  } else { 
    $statimg = "<img align=absmiddle src=images/16/lightbulb_off.png alt='Host Down'>"; 
    $background_image = "images/boxbgpink.png"; 
    $background_color = "#ffdddd";
  }

  if($ints || $services) { $background_color = "#ffddaa"; }

  if($rebooted) { $statimg = "<img align=absmiddle src=images/16/lightning.png alt='Host Rebooted'>"; }

  if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }

  if(devicepermitted($node)) {

  list ($first, $second, $third) = explode(".", $host);

  $shorthost = $first;
  if(strlen($first.".".$second) < 16) { $shorthost = $first.".".$second; }

  $device['device_id'] = $node;

  $errorboxes .= "
    <div style='border: solid 2px #D0D0D0; float: left; padding: 5px; width: 120px; height: 75px; background: $background_color; margin: 4px;'>
      <center><strong>".generatedevicelink($device, $shorthost)."</strong><br />";

  if(hoststatus($node)) {
    $errorboxes .= "  <span class=body-date-1>".formatuptime($uptime, short)."</span><br />";
    
    if($rebooted) { $errorboxes .= "  <div style='font-size: 14px; font-weight: bold; margin: 4px; color: #2a2;'>Rebooted</div><br />"; }  

  } else { $errorboxes .= "  <div style='font-size: 14px; font-weight: bold; margin: 5px; color: #f66;'>Device<br />Unreachable</div><br />"; }

#  $errorboxes .= " <img src='images/16/disconnect.png' align=absmiddle> <a $intpop><b>$ints</b></a>
#		   <img src='images/16/cog_error.png' align=absmiddle> <a $srvpop><b>$services</b></a>";

  if($ints) { $errorboxes .= "<div style='font-size: 11px;'><a $intpop>$ints Down Interfaces</a></div>"; }
  if($services) { $errorboxes .= "<div style='font-size: 11px;'><a $srvpop>$services Down Services</a></div>"; }


  $errorboxes .= " </center></div>";
   

#  echo("<tr bgcolor=$bg>
#          <td><a href='?page=device&id=$node' $mouseover>$host</a></td>
#          <td align=center>$statimg</td>
#          <td align=center><a $intpop>$ints</a></td>
#          <td align=center><a $srvpop>$services</a></td></tr>");
#
  }
  unset($int, $ints, $intlist, $intpop, $srv, $srvlist, $srvname, $srvpop);
}

#echo("</table>");

#echo("
#    </td>
#    <td bgcolor=#e5e5e5 width=400 valign=top>
 
echo("

	<div style='clear: both;'>$errorboxes</div> <div style='margin: 4px; clear: both;'>  

<h3>Recent Syslog Messages</h3>

");

$sql = "SELECT *, DATE_FORMAT(datetime, '%D %b %T') AS date from syslog ORDER BY datetime DESC LIMIT 20";
$query = mysql_query($sql);
echo("<table cellspacing=0 cellpadding=2 width=100%>");
while($entry = mysql_fetch_array($query)) { include("includes/print-syslog.inc"); }
echo("</table>");


echo("</div>

   </td>
   <td bgcolor=#e5e5e5 width=275 valign=top>");


/// this stuff can be customised to show whatever you want....

if($_SESSION['userlevel'] >= '5') {

  $sql  = "select * from interfaces as I, devices as D WHERE `ifAlias` like 'L2TP: %' AND I.device_id = D.device_id AND D.hostname LIKE '%";
  $sql .= $config['mydomain'] . "' ORDER BY I.ifAlias";
  $query = mysql_query($sql);
  unset ($seperator);
  while($interface = mysql_fetch_array($query)) {
    $interfaces['l2tp'] .= $seperator . $interface['interface_id'];
    $seperator = ",";
  }

  $sql  = "select * from interfaces as I, devices as D WHERE `ifAlias` like 'Transit: %' AND I.device_id = D.device_id AND D.hostname LIKE '%";
  $sql .= $config['mydomain'] . "' ORDER BY I.ifAlias";
  $query = mysql_query($sql);
  unset ($seperator);
  while($interface = mysql_fetch_array($query)) {
    $interfaces['transit'] .= $seperator . $interface['interface_id'];
    $seperator = ",";
  }

  $sql  = "select * from interfaces as I, devices as D WHERE `ifAlias` like 'Server: thlon-pbx%' AND I.device_id = D.device_id AND D.hostname LIKE '%";
  $sql .= $config['mydomain'] . "' ORDER BY I.ifAlias";
  $query = mysql_query($sql);
  unset ($seperator);
  while($interface = mysql_fetch_array($query)) {
    $interfaces['voip'] .= $seperator . $interface['interface_id'];
    $seperator = ",";
  }

  if($interfaces['transit']) {
    echo("<a onmouseover=\"return overlib('<img src=\'graph.php?type=multi_bits&interfaces=".$interfaces['transit'].
    "&from=".$day."&to=".$now."&width=400&height=150\'>', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 250);\" onmouseout=\"return nd();\"  >".
    "<div style='font-size: 18px; font-weight: bold;'>Internet Transit</div>".
    "<img src='graph.php?type=multi_bits&interfaces=".$interfaces['transit'].
    "&from=".$day."&to=".$now."&width=200&height=100'></a>");
  }

  if($interfaces['l2tp']) {
    echo("<a onmouseover=\"return overlib('<img src=\'graph.php?type=multi_bits&interfaces=".$interfaces['l2tp'].
    "&from=".$day."&to=".$now."&width=400&height=150\'>', LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 250);\" onmouseout=\"return nd();\"  >".
    "<div style='font-size: 18px; font-weight: bold;'>L2TP ADSL</div>".
    "<img src='graph.php?type=multi_bits&interfaces=".$interfaces['l2tp'].
    "&from=".$day."&to=".$now."&width=200&height=100'></a>");
  }

  if($interfaces['voip']) {
    echo("<a onmouseover=\"return overlib('<img src=\'graph.php?type=multi_bits&interfaces=".$interfaces['voip'].
    "&from=".$day."&to=".$now."&width=400&height=150\'>', LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 250);\" onmouseout=\"return nd();\"  >".
    "<div style='font-size: 18px; font-weight: bold;'>VoIP to PSTN</div>".
    "<img src='graph.php?type=multi_bits&interfaces=".$interfaces['voip'].
    "&from=".$day."&to=".$now."&width=200&height=100'></a>");
  }

}

/// END VOSTRON

?>
</td>

  </tr>
  <tr>
</tr></table>
