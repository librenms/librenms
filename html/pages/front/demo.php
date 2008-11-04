
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
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Device Down</span><br /> 
      <span class=body-date-1>".truncate($device['location'], 20)."</span>
      </center></div>");

}

$sql = mysql_query("SELECT * FROM `interfaces` AS I, `devices` AS D WHERE I.device_id = D.device_id AND D.status = '1' AND ifOperStatus = 'down' AND ifAdminStatus = 'up' AND D.ignore = '0' AND I.ignore = '0'");
while($interface = mysql_fetch_array($sql)){

      echo("<div style='border: solid 2px #D0D0D0; float: left; padding: 5px; width: 120px; height: 90px; background: #ffddaa; margin: 4px;'>
      <center><strong>".generatedevicelink($interface, shorthost($interface['hostname']))."</strong><br />
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Port Down</span> 
      <strong>".generateiflink($interface, makeshortif($interface['ifDescr']))."</strong> <br />
      <span class=body-date-1>".truncate($interface['ifAlias'], 20)."</span>
      </center></div>");

}

$sql = mysql_query("SELECT * FROM `services` AS S, `devices` AS D WHERE S.service_host = D.device_id AND  D.status = '1' AND service_status = 'down' AND D.ignore = '0' AND S.service_ignore = '0'");
while($service = mysql_fetch_array($sql)){

      echo("<div style='border: solid 2px #D0D0D0; float: left; padding: 5px; width: 120px; height: 90px; background: #ffddaa; margin: 4px;'>
      <center><strong>".generatedevicelink($service, shorthost($service['hostname']))."</strong><br />
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Service Down</span> 
      <strong>".$service['service_type']."</strong><br />
      <span class=body-date-1>".truncate($interface['ifAlias'], 20)."</span>
      </center></div>");

}

$sql = mysql_query("SELECT * FROM `devices` AS D, bgpPeers AS B WHERE  D.status = '1' AND bgpPeerState != 'established' AND B.device_id = D.device_id");
while($peer = mysql_fetch_array($sql)){

      echo("<div style='border: solid 2px #d0D0D0; float: left; padding: 5px; width: 120px; height: 90px; background: #ffddaa; margin: 4px;'>
      <center><strong>".generatedevicelink($peer, shorthost($peer['hostname']))."</strong><br />
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>BGP Down</span> 
      <strong>".$peer['bgpPeerIdentifier']."</strong> <br />
      <span class=body-date-1>AS".$peer['bgpPeerRemoteAs']." ".truncate($peer['astext'], 10)."</span>
      </center></div>");

}

$sql = mysql_query("SELECT * FROM `devices` AS D, devices_attribs AS A WHERE A.device_id = D.device_id AND D.status = '1' AND A.attrib_type = 'uptime' AND A.attrib_value < '84600'");
while($device = mysql_fetch_array($sql)){

      echo("<div style='border: solid 2px #d0D0D0; float: left; padding: 5px; width: 120px; height: 90px; background: #ddffdd; margin: 4px;'>
      <center><strong>".generatedevicelink($device, shorthost($device['hostname']))."</strong><br />
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #090;'>Device<br />Rebooted</span><br /> 
      <span class=body-date-1>".formatUptime($device['attrib_value'])."</span>
      </center></div>");

}



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

#if($_SESSION['userlevel'] >= '5') {

echo("<a onmouseover=\"return overlib('<img src=\'graph.php?type=bits&if=36".
    "&from=".$day."&to=".$now."&width=400&height=150\'>', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 250);\" onmouseout=\"return nd();\"  >".
    "<div style='font-size: 18px; font-weight: bold;'>Alpha Traffic</div>".
    "<img src='graph.php?type=bits&if=36".
    "&from=".$day."&to=".$now."&width=200&height=100'></a>");

echo("<div style='clear: both; margin-top: 10px;'></div>");

echo("<a onmouseover=\"return overlib('<img src=\'graph.php?type=unixfs&id=54".
    "&from=".$day."&to=".$now."&width=400&height=150\'>', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 250);\" onmouseout=\"return nd();\"  >".
    "<div style='font-size: 18px; font-weight: bold;'>Alpha Storage</div>".
    "<img src='graph.php?type=unixfs&id=54".
    "&from=".$day."&to=".$now."&width=200&height=100'></a>");

#}

?>
</td>

  </tr>
  <tr>
</tr></table>
