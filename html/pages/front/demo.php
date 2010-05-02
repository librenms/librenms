
<table border=0 cellpadding=10 cellspacing=10 width=100%>
  <tr>
    <td bgcolor=#e5e5e5 valign=top>
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
   if(devicepermitted($device['device_id'])) {
      echo("<div style='text-align: center; margin: 2px; border: solid 2px #d0D0D0; float: left; margin-right: 2px; padding: 3px; width: 118px; height: 85px; background: #ffbbbb;'>
       <strong>".generatedevicelink($device, shorthost($device['hostname']))."</strong><br />
       <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Device Down</span><br />
       <span class=body-date-1>".truncate($device['location'], 35)."</span>
      </div>");
   }
}

if($config['warn']['ifdown']) {

$sql = mysql_query("SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id AND ifOperStatus = 'down' AND ifAdminStatus = 'up' AND D.ignore = '0' AND I.ignore = '0'");
while($interface = mysql_fetch_array($sql)){
   if(interfacepermitted($interface['interface_id'])) {
      echo("<div style='text-align: center; margin: 2px; border: solid 2px #D0D0D0; float: left; margin-right: 2px; padding: 3px; width: 118px; height: 85px; background: #ffddaa;'>
       <strong>".generatedevicelink($interface, shorthost($interface['hostname']))."</strong><br />
       <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Port Down</span><br />
       <strong>".generateiflink($interface, makeshortif($interface['ifDescr']))."</strong><br />
       <span class=body-date-1>".truncate($interface['ifAlias'], 15)."</span>
      </div>");
   }
}

}

$sql = mysql_query("SELECT * FROM `services` AS S, `devices` AS D WHERE S.device_id = D.device_id AND service_status = 'down' AND D.ignore = '0' AND S.service_ignore = '0'");
while($service = mysql_fetch_array($sql)){
   if(devicepermitted($service['device_id'])) {
      echo("<div style='text-align: center; margin: 2px; border: solid 2px #D0D0D0; float: left; margin-right: 2px; padding: 3px; width: 118px; height: 85px; background: #ffddaa;'>
      <strong>".generatedevicelink($service, shorthost($service['hostname']))."</strong><br />
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Service Down</span><br />
      <strong>".$service['service_type']."</strong><br />
      <span class=body-date-1>".truncate($interface['ifAlias'], 15)."</span>
      </center></div>");
   }
}

$sql = mysql_query("SELECT * FROM `devices` AS D, bgpPeers AS B WHERE bgpPeerAdminStatus = 'start' AND bgpPeerState != 'established' AND B.device_id = D.device_id");
while($peer = mysql_fetch_array($sql)){
   if(devicepermitted($peer['device_id'])) {
      echo("<div style='text-align: center; margin: 2px; border: solid 2px #D0D0D0; float: left; margin-right: 2px; padding: 3px; width: 118px; height: 85px; background: #ffddaa;'>
      <strong>".generatedevicelink($peer, shorthost($peer['hostname']))."</strong><br />
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>BGP Down</span><br /> 
      <strong>".$peer['bgpPeerIdentifier']."</strong><br />
      <span class=body-date-1>AS".$peer['bgpPeerRemoteAs']." ".truncate($peer['astext'], 10)."</span>
      </div>");
   }
}

$sql = mysql_query("SELECT * FROM devices_attribs AS A, `devices` AS D WHERE A.attrib_value < '84600' AND A.attrib_type = 'uptime' AND A.device_id = D.device_id AND ignore = '0' AND disabled = '0'");
while($device = mysql_fetch_array($sql)){
   if(devicepermitted($device['device_id']) && $device['attrib_value'] < "84600" && $device['attrib_type'] == "uptime" ) {
      echo("<div style='text-align: center; margin: 2px; border: solid 2px #D0D0D0; float: left; margin-right: 2px; padding: 3px; width: 118px; height: 85px; background: #ddffdd;'>
      <strong>".generatedevicelink($device, shorthost($device['hostname']))."</strong><br />
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #090;'>Device<br />Rebooted</span><br />
      <span class=body-date-1>".formatUptime($device['attrib_value'])."</span>
      </div>");
   }
}


echo("

	<div style='clear: both;'>$errorboxes</div> <div style='margin: 0px; clear: both;'>  

<h3>Recent Syslog Messages</h3>

");

$sql = "SELECT *, DATE_FORMAT(timestamp, '%D %b %T') AS date from syslog,devices WHERE syslog.device_id = devices.device_id ORDER BY seq DESC LIMIT 20";
$query = mysql_query($sql);
echo("<table cellspacing=0 cellpadding=2 width=100%>");
while($entry = mysql_fetch_array($query)) { include("includes/print-syslog.inc"); }
echo("</table>");


echo("</div>

   </td>
   <td bgcolor=#e5e5e5 width=470 valign=top>");


/// this stuff can be customised to show whatever you want....


  $ports['fileserver'] = "78";
  $ports['broadband'] = "228,251,182";
  $ports['homeserver'] = "256,245,74";

  echo("<div style=' margin-bottom: 5px;'>");

  if($ports['fileserver']) {

    echo("<div style='width: 470px;'>");
    echo("<div style='font-size: 16px; font-weight: bold; color: #555555;'>Central Fileserver</div>");

    $graph_array['height'] = "100";
    $graph_array['width']  = "385";
    $graph_array['to']     = $now;
    $graph_array['port']   = $ports['fileserver'];
    $graph_array['type']   = "port_bits";
    $graph_array['from']   = $day;
    $graph_array['legend'] = "no";

    $graph_array['popup_title'] = "Central Fileserver";

    print_graph_popup($graph_array);

    echo("</div>");

  }

  echo("</div>");

  echo("<div style=' margin-bottom: 5px;'>");

    echo("<div style='width: 235px; float: left;'>
    <a onmouseover=\"return overlib('\
    <img src=\'graph.php?type=port_bits&port=182&from=".$day."&to=".$now."&width=400&height=150&inverse=0&legend=1\'>\
    <img src=\'graph.php?type=port_bits&port=182&from=".$week."&to=".$now."&width=400&height=150&inverse=0&legend=1\'>\
    ', LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".    "
    <div style='font-size: 16px; font-weight: bold; color: #555555;'>NE61 Broadband</div>".
    "<img src='graph.php?type=port_bits&port=182&from=".$day."&to=".$now."&width=155&height=100&inverse=0&legend=no'></a></div>");

    echo("<div style='width: 235px; float: right;'>
    <a onmouseover=\"return overlib('\
    <img src=\'graph.php?type=port_bits&port=74&from=".$day."&to=".$now."&width=400&height=150&inverse=0&legend=1\'>\
    <img src=\'graph.php?type=port_bits&port=74&from=".$week."&to=".$now."&width=400&height=150&inverse=0&legend=1\'>\
    ', LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".    "
    <div style='font-size: 16px; font-weight: bold; color: #555555;'>NE61 Server</div>".
    "<img src='graph.php?type=port_bits&port=74&from=".$day."&to=".$now."&width=155&height=100&inverse=0&legend=no'></a></div>");

  echo("</div>");

  echo("<div style=' margin-bottom: 5px;'>");

    echo("<div style='width: 235px; float: left;'>
    <a onmouseover=\"return overlib('\
    <img src=\'graph.php?type=port_bits&port=251&from=".$day."&to=".$now."&width=400&height=150&inverse=0&legend=1\'>\
    <img src=\'graph.php?type=port_bits&port=251&from=".$week."&to=".$now."&width=400&height=150&inverse=0&legend=1\'>\
    ', LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".    "
    <div style='font-size: 16px; font-weight: bold; color: #555555;'>DE56 Broadband</div>".
    "<img src='graph.php?type=port_bits&port=251&from=".$day."&to=".$now."&width=155&height=100&inverse=0&legend=no'></a></div>");

    echo("<div style='width: 235px; float: right;'>
    <a onmouseover=\"return overlib('\
    <img src=\'graph.php?type=port_bits&port=256&from=".$day."&to=".$now."&width=400&height=150&inverse=0&legend=1\'>\
    <img src=\'graph.php?type=port_bits&port=256&from=".$week."&to=".$now."&width=400&height=150&inverse=0&legend=1\'>\
    ', LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".    "
    <div style='font-size: 16px; font-weight: bold; color: #555555;'>DE56 Server</div>".
    "<img src='graph.php?type=port_bits&port=256&from=".$day."&to=".$now."&width=155&height=100&inverse=0&legend=no'></a></div>");

  echo("</div>");

  echo("<div style=' margin-bottom: 5px;'>");

    echo("<div style='width: 235px; float: left;'>
    <a onmouseover=\"return overlib('\
    <img src=\'graph.php?type=port_bits&port=228&from=".$day."&to=".$now."&width=400&height=150&inverse=0&legend=1\'>\
    <img src=\'graph.php?type=port_bits&port=228&from=".$week."&to=".$now."&width=400&height=150&inverse=0&legend=1\'>\
    ', LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".    "
    <div style='font-size: 16px; font-weight: bold; color: #555555;'>DE24 Broadband</div>".
    "<img src='graph.php?type=port_bits&port=228&from=".$day."&to=".$now."&width=155&height=100&inverse=0&legend=no'></a></div>");

    echo("<div style='width: 235px; float: right;'>
    <a onmouseover=\"return overlib('\
    <img src=\'graph.php?type=port_bits&port=245&from=".$day."&to=".$now."&width=400&height=150&inverse=0&legend=1\'>\
    <img src=\'graph.php?type=port_bits&port=245&from=".$week."&to=".$now."&width=400&height=150&inverse=0&legend=1\'>\
    ', LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".    "
    <div style='font-size: 16px; font-weight: bold; color: #555555;'>DE24 Server</div>".
    "<img src='graph.php?type=port_bits&port=245&from=".$day."&to=".$now."&width=155&height=100&inverse=0&legend=no'></a></div>");

  echo("</div>");



?>
</td>

  </tr>
  <tr>
</tr></table>
