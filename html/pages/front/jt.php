
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

$sql = mysql_query("SELECT * FROM `interfaces` AS I, `devices` AS D WHERE I.device_id = D.device_id AND ifOperStatus = 'down' AND ifAdminStatus = 'up' AND D.ignore = '0' AND I.ignore = '0'");
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

$sql = mysql_query("SELECT * FROM `services` AS S, `devices` AS D WHERE S.service_host = D.device_id AND service_status = 'down' AND D.ignore = '0' AND S.service_ignore = '0'");
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

$sql = "SELECT *, DATE_FORMAT(datetime, '%D %b %T') AS date from syslog,devices WHERE syslog.device_id = devices.device_id ORDER BY seq DESC LIMIT 20";
$query = mysql_query($sql);
echo("<table cellspacing=0 cellpadding=2 width=100%>");
while($entry = mysql_fetch_array($query)) { include("includes/print-syslog.inc"); }
echo("</table>");


echo("</div>

   </td>
   <td bgcolor=#e5e5e5 width=470 valign=top>");


/// this stuff can be customised to show whatever you want....

if($_SESSION['userlevel'] >= '5') 
{

  $sql  = "select * from interfaces as I, devices as D WHERE `ifAlias` like 'Transit: %' AND I.device_id = D.device_id ORDER BY I.ifAlias";
  $query = mysql_query($sql);
  unset ($seperator);
  while($interface = mysql_fetch_array($query)) {
    $interfaces['transit'] .= $seperator . $interface['interface_id'];
    $seperator = ",";
  }

  $sql  = "select * from interfaces as I, devices as D WHERE `ifAlias` like 'Peering: %' AND I.device_id = D.device_id ORDER BY I.ifAlias";
  $query = mysql_query($sql);
  unset ($seperator);
  while($interface = mysql_fetch_array($query)) {
    $interfaces['peering'] .= $seperator . $interface['interface_id'];
    $seperator = ",";
  }


  $interfaces['broadband'] = "3294,3295,688,3534";
  $interfaces['wave_broadband'] = "827";

  $interfaces['new_broadband'] = "3659,4149,4121,4108,3676,4135";


  echo("<div style=' margin-bottom: 5px;'>");

  if($interfaces['peering'] && $interfaces['transit']) {
    echo("<div style='width: 235px; '>
    <a href='internet/' onmouseover=\"return overlib('\
    <img src=\'graph.php?type=multi_bits_duo&interfaces=".$interfaces['peering']."&interfaces_b=".$interfaces['transit']."&from=".$day."&to=".$now."&width=400&height=150\'>\
    <img src=\'graph.php?type=multi_bits_duo&interfaces=".$interfaces['peering']."&interfaces_b=".$interfaces['transit']."&from=".$week."&to=".$now."&width=400&height=150\'>\
    ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<div style='font-size: 16px; font-weight: bold; color: #555555;'>Aggregate Internet Traffic</div>".
    "<img src='graph.php?type=multi_bits_duo&interfaces=".$interfaces['peering']."&interfaces_b=".$interfaces['transit'].
    "&from=".$day."&to=".$now."&width=385&height=100&legend=no'></a></div>");
  }

  echo("</div>");

  echo("<div style=' margin-bottom: 5px;'>");

  if($interfaces['transit']) {
    echo("<div style='width: 235px; float: left;'>
    <a href='iftype/transit/' onmouseover=\"return overlib('\
    <img src=\'graph.php?type=multi_bits&interfaces=".$interfaces['transit']."&from=".$day."&to=".$now."&width=400&height=150\'>\
    <img src=\'graph.php?type=multi_bits&interfaces=".$interfaces['transit']."&from=".$week."&to=".$now."&width=400&height=150\'>\
    ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<div style='font-size: 16px; font-weight: bold; color: #555555;'>Internet Transit</div>".
    "<img src='graph.php?type=multi_bits&interfaces=".$interfaces['transit'].
    "&from=".$day."&to=".$now."&width=155&height=100&legend=no'></a></div>");
  }

  if($interfaces['peering']) {
    echo("<div style='width: 235px; float: right;'>
    <a href='iftype/peering/' onmouseover=\"return overlib('\
    <img src=\'graph.php?type=multi_bits&interfaces=".$interfaces['peering']."&from=".$day."&to=".$now."&width=400&height=150\'>\
    <img src=\'graph.php?type=multi_bits&interfaces=".$interfaces['peering']."&from=".$week."&to=".$now."&width=400&height=150\'>\
    ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<div style='font-size: 16px; font-weight: bold; color: #555555;'>Internet Peering</div>".
    "<img src='graph.php?type=multi_bits&interfaces=".$interfaces['peering'].
    "&from=".$day."&to=".$now."&width=155&height=100&legend=no'></a></div>");
  }

  echo("</div>");

  echo("<div style=' margin-bottom: 5px;'>");

  if($interfaces['broadband'] && $interfaces['wave_broadband'] && $interfaces['new_broadband']) {
    echo("<div style='width: 466px; '>
    <a href='broadband/' onmouseover=\"return overlib('\
    <img src=\'graph.php?type=multi_bits_trio&interfaces=".$interfaces['broadband']."&interfaces_b=".$interfaces['new_broadband']."&interfaces_c=".$interfaces['wave_broadband']."&from=".$day."&to=".$now."&width=400&height=150&inverse=c\'>\
    <img src=\'graph.php?type=multi_bits_trio&interfaces=".$interfaces['broadband']."&interfaces_b=".$interfaces['new_broadband']."&interfaces_c=".$interfaces['wave_broadband']."&from=".$week."&to=".$now."&width=400&height=150&inverse=c\'>\
    ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<div style='font-size: 16px; font-weight: bold; color: #555555;'>Aggregate Broadband Traffic</div>".
    "<img src='graph.php?type=multi_bits_trio&interfaces=".$interfaces['broadband']."&interfaces_b=".$interfaces['new_broadband']."&interfaces_c=".$interfaces['wave_broadband'].
    "&from=".$day."&to=".$now."&width=385&height=100&legend=no&inverse=c'></a></div>");
  }



  echo("<div style=' margin-bottom: 5px;'>");

  if($interfaces['broadband']) {
    echo("<div style='width: 235px; float: left;'>
    <a onmouseover=\"return overlib('\
    <img src=\'graph.php?type=multi_bits&interfaces=".$interfaces['broadband']."&from=".$day."&to=".$now."&width=400&height=150\'>\
    <img src=\'graph.php?type=multi_bits&interfaces=".$interfaces['broadband']."&from=".$week."&to=".$now."&width=400&height=150\'>\
    ', LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<div style='font-size: 16px; font-weight: bold; color: #555555;'>Jersey Broadband ATM</div>".
    "<img src='graph.php?type=multi_bits&interfaces=".$interfaces['broadband'].
    "&from=".$day."&to=".$now."&width=155&height=100&legend=no'></a></div>");
  }

  echo("<div style=' margin-bottom: 5px;'>");

  if($interfaces['new_broadband']) {
    echo("<div style='width: 235px; float: left;'>
    <a onmouseover=\"return overlib('\
    <img src=\'graph.php?type=multi_bits&interfaces=".$interfaces['new_broadband']."&from=".$day."&to=".$now."&width=400&height=150&inverse=0\'>\
    <img src=\'graph.php?type=multi_bits&interfaces=".$interfaces['new_broadband']."&from=".$week."&to=".$now."&width=400&height=150&inverse=0\'>\
    ', LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".
    "<div style='font-size: 16px; font-weight: bold; color: #555555;'>Jersey Broadband NGN</div>".
    "<img src='graph.php?type=multi_bits&interfaces=".$interfaces['new_broadband']."&from=".$day."&to=".$now."&width=155&height=100&inverse=0&legend=no'></a></div>");
  }

  echo("</div>");


  if($interfaces['wave_broadband']) {
    echo("<div style='width: 235px; float: left;'>
    <a onmouseover=\"return overlib('\
    <img src=\'graph.php?type=port_bits&port=".$interfaces['wave_broadband']."&from=".$day."&to=".$now."&width=400&height=150&inverse=1&legend=1\'>\
    <img src=\'graph.php?type=port_bits&port=".$interfaces['wave_broadband']."&from=".$week."&to=".$now."&width=400&height=150&inverse=1&legend=1\'>\
    ', LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >".    "
    <div style='font-size: 16px; font-weight: bold; color: #555555;'>Wave Broadband</div>".
    "<img src='graph.php?type=port_bits&port=".$interfaces['wave_broadband']."&from=".$day."&to=".$now."&width=155&height=100&inverse=1&legend=no'></a></div>");
  }

  echo("</div>");

}

?>
</td>

  </tr>
  <tr>
</tr></table>
