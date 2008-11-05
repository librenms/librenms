<?

if($_GET['id']) {$_GET['id'] = mres($_GET['id']); } 

if(devicepermitted($_GET['id'])) {

$selected['iface'] = "selected";

if(!$_GET['section']) { 
  $_GET['section'] = "dev-overview"; 
}
$section = mres($_GET['section']);
$section = str_replace(".", "", $section);
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

if(@mysql_result(mysql_query("select count(*) from vrfs WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') {
  echo("
<li class=" . $select['dev-vrfs'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-vrfs' >
    <img src='images/16/layers.png' align=absmiddle border=0> VRFs
  </a>
</li>");
}


if($config['enable_bgp'] && $device['bgpLocalAs']) {
  echo("
<li class=" . $select['dev-bgp'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-bgp' >
    <img src='images/16/link.png' align=absmiddle border=0> BGP
  </a>
</li>");
}

if(@mysql_result(mysql_query("select count(interface_id) from interfaces WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') {
  echo("
<li class=" . $select['dev-ifs'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-ifs' >
    <img src='images/16/server_link.png' align=absmiddle border=0> Ports
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

if(@mysql_result(mysql_query("SELECT * FROM `entPhysical` WHERE device_id = '".$_GET['id']."'"), 0) > '0') {

  echo("<li class=" . $select['dev-entphysical'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-entphysical'>
    <img src='images/16/bricks.png' align=absmiddle border=0> Inventory
  </a>
</li>
");


}

if(mysql_result(mysql_query("select count(temp_id) from temperature WHERE temp_host = '" . $device['device_id'] . "'"), 0) > '0') {
  echo("
<li class=" . $select['dev-temp'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-temp'>
    <img src='images/16/weather_sun.png' align=absmiddle border=0> Temps
  </a>
</li>
");
}

if(mysql_result(mysql_query("select count(storage_id) from storage WHERE host_id = '" . $device['device_id'] . "'"), 0) > '0') {
  echo("
<li class=" . $select['dev-storage'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-storage'>
    <img src='images/16/database.png' align=absmiddle border=0> Storage
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
    <img src='images/16/report_magnify.png' align=absmiddle border=0> Events
  </a>
</li>");

if($config['enable_syslog']) { echo("
<li class=" . $select['dev-syslog'] . ">
  <a href='?page=device&id=" . $device['device_id'] . "&section=dev-syslog'>
    <img src='images/16/printer.png' align=absmiddle border=0> Syslog
  </a>
</li>
"); }

if($_SESSION[userlevel] >= "5") {
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

include("pages/device/$section.inc");

echo("</div>
");
}

} else { echo("<span class=alert>You do not have the necessary access permissions to view this device.</span>"); }
?>

