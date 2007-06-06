<?

$selected['iface'] = "selected";

if(!$_GET['section']) { 
  $_GET['section'] = "dev-overview"; 
}
$section = $_GET['section'];
$select[$section] = "selected";


$device_query = mysql_query("SELECT * FROM `devices` WHERE `device_id` = '" . $_GET['id'] . "'");
while($device = mysql_fetch_array($device_query)) {
  echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%>");
  include("includes/device-header.inc");
  echo("</table><br />");

echo("<div class=mainpane>");

echo("
<ul id='maintab' class='shadetabs'>
<li class=" . $select['dev-overview'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-overview' >
    <img src='images/16/server_lightning.png' align=absmiddle border=0> Overview
  </a>
</li>");

if(@mysql_result(mysql_query("select count(vlan_id) from vlans WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') {
  echo("
<li class=" . $select['dev-vlans'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-vlans' >
    <img src='images/16/vlans.png' align=absmiddle border=0> VLANs
  </a>
</li>");
}

if(@mysql_result(mysql_query("select count(interface_id) from interfaces WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') {
  echo("
<li class=" . $select['dev-ifs'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-ifs' >
    <img src='images/16/server_link.png' align=absmiddle border=0> Port Details
  </a>
</li>
<li class=" . $select['dev-ifgraphs'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-ifgraphs'>
    <img src='images/16/chart_curve_link.png' align=absmiddle border=0> Port Graphs
  </a>
</li>");
}

echo("<li class=" . $select['dev-graphs'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-graphs'>
    <img src='images/16/server_chart.png' align=absmiddle border=0> Host Graphs
  </a>
</li>
");

if(mysql_result(mysql_query("select count(temp_id) from temperature WHERE temp_host = '" . $device['device_id'] . "'"), 0) > '0') {
  echo("
<li class=" . $select['dev-temp'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-temp'>
    <img src='images/16/weather_sun.png' align=absmiddle border=0> Temps
  </a>
</li>
");
}

if(mysql_result(mysql_query("select count(service_id) from services WHERE service_host = '" . $device['device_id'] . "'"), 0) > '0') {
  echo("
<li class=" . $select['dev-srv'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-srv'>
    <img src='images/16/server_cog.png' align=absmiddle border=0> Services
  </a>
</li>
");
}

echo("
<li class=" . $select['dev-events'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-events'>
    <img src='images/16/report_magnify.png' align=absmiddle border=0> Eventlog
  </a>
</li>");

echo("
<li class=" . $select['dev-syslog'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-syslog'>
    <img src='images/16/printer.png' align=absmiddle border=0> Syslog
  </a>
</li>
");

if($_SESSION[userlevel] > "5") {
  echo("
<li class=" . $select['dev-edit'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-edit'>
    <img src='images/16/server_edit.png' align=absmiddle border=0> Settings
  </a>
</li>
");
}


echo("</ul>");
echo("
<div class=contentstyle>");

include("pages/$page/$section.inc");

echo("</div>
");
}
?>

