<?php

$sql = mysql_query("SELECT * FROM `devices` AS D, `devices_attribs` AS U WHERE D.device_id = U.device_id AND U.attrib_type = 'uptime' AND U.attrib_value > '0' AND U.attrib_value < '86400'");
while($device = mysql_fetch_array($sql)){
  $rebooted[] = "$device[device_id]";
}
?>

<table border=0 cellpadding=15 cellspacing=10 width=100%>
  <tr>
    <td bgcolor=#e5e5e5 width=50% valign=top>
      <div class=graphhead>Nodes with Outages</div>
      <table width=100% border=0><tr><td></td><td width=35 align=center><div class=tablehead>Host</div></td><td align=center width=35><div class=tablehead>Int</div></td><td align=center width=35><div class=tablehead>Srv</div></tr>
<?php

$nodes = array();

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
  if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }
  echo("<tr bgcolor=$bg>
          <td><a href='?page=device&id=$node' $mouseover>$host</a></td>
          <td align=center>$statimg</td>
          <td align=center><a $intpop>$ints</a></td>
          <td align=center><a $srvpop>$services</a></td></tr>");

  unset($int, $ints, $intlist, $intpop, $srv, $srvlist, $srvname, $srvpop);

}

echo("</table>");

?>
    </td>
    <td bgcolor=#e5e5e5 width=50% valign=top>
      <div class=graphhead>Network Infrastructure Diagram</div>
      <img src="network.png" alt="Auto-generated network diagram">
   </td>
  </tr>
</table>
