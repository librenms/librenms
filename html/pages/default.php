
<table border=0 cellpadding=10 cellspacing=10 width=100%>
  <tr>
    <td bgcolor=#e5e5e5 valign=top>
      <table width=100% border=0><tr><td><div style="margin-bottom: 5px; font-size: 18px; font-weight: bold;">Devices with Alerts</div></td><td width=35 align=center><div class=tablehead>Host</div></td><td align=center width=35><div class=tablehead>Int</div></td><td align=center width=35><div class=tablehead>Srv</div></tr>
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


$sql = mysql_query("SELECT * FROM `devices` WHERE `status` = '0'");
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

$sql = mysql_query("SELECT * FROM `interfaces` AS I, `devices` AS D WHERE I.device_id = D.device_id AND ifOperStatus = 'down' AND ifAdminStatus = 'up'");
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

$sql = mysql_query("SELECT D.device_id  FROM `services` AS S, `devices` AS D WHERE S.service_host = D.device_id AND service_status = 'down'");
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

foreach($nodes as $node) {

  unset($srvpop);

  $host = gethostbyid($node);

  $ints = mysql_result(mysql_query("SELECT count(*) FROM `interfaces` WHERE `ifOperStatus` = 'down' AND `ifAdminStatus` = 'up' AND `device_id` = '$node'"),0);
  $services = mysql_result(mysql_query("SELECT count(service_id) FROM `services` WHERE `service_status` = '0' AND `service_host` = '$node'"),0);

  $intlist = array();
  $sql = mysql_query("SELECT `ifDescr`, `ifAlias` FROM interfaces WHERE `ifOperStatus` = 'down' AND `ifAdminStatus` = 'up' AND `device_id` = '$node'");

  $rebooted = mysql_result(mysql_query("SELECT attrib_value FROM `devices` AS D, `devices_attribs` AS A WHERE D.device_id = '$node' AND D.status = '1' AND A.device_id = D.device_id AND A.attrib_type = 'uptime' AND A.attrib_value > '0' AND A.attrib_value < '86400'"),0);

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

  if(hoststatus($node)) { $statimg = "<img align=absmiddle src=images/16/lightbulb.png alt='Host Up'>"; } 
                   else { $statimg = "<img align=absmiddle src=images/16/lightbulb_off.png alt='Host Down'>";}
  if($rebooted) { $statimg = "<img align=absmiddle src=images/16/lightning.png alt='Host Rebooted'>"; }

  if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }

  if(devicepermitted($node)) {

  echo("<tr bgcolor=$bg>
          <td><a href='?page=device&id=$node' $mouseover>$host</a></td>
          <td align=center>$statimg</td>
          <td align=center><a $intpop>$ints</a></td>
          <td align=center><a $srvpop>$services</a></td></tr>");

  }
  unset($int, $ints, $intlist, $intpop, $srv, $srvlist, $srvname, $srvpop);
}

echo("</table>");

echo("    </td>
    <td bgcolor=#e5e5e5 width=400 valign=top>
  ");

  if($_SESSION['userlevel'] >= '5') {
    echo("
      <div style='font-size: 18px; font-weight: bold;'>Network Infrastructure Diagram</div>
      <img style='margin-top: 10px;' src='network.png' alt='Auto-generated network diagram'>
    ");
  }
?>

   </td>
   <td bgcolor=#e5e5e5 width=275 valign=top>

<?php

/// VOSTRON

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
    "<img src='http://network.vostron.net/graph.php?type=multi_bits&interfaces=".$interfaces['transit'].
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
