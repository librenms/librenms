<?

if($_GET['id']) {$_GET['id'] = mres($_GET['id']); } 

if(devicepermitted($_GET['id'])) {

$selected['iface'] = "selected";

if(!$_GET['section']) { 
  $_GET['section'] = "overview"; 
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
");


#echo("
#<ul id='maintab' class='shadetabs'>
#<li class=" . $select['overview'] . ">
#  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/overview/'>
#    <img src='images/16/server_lightning.png' align=absmiddle border=0> Overview
#  </a>
#</li>");

echo("<li class=" . $select['graphs'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/graphs/'>
    <img src='images/16/server_chart.png' align=absmiddle border=0> Graphs
  </a>
</li>
");

$health =  mysql_result(mysql_query("select count(*) from storage WHERE host_id = '" . $device['device_id'] . "'"), 0) +
           mysql_result(mysql_query("select count(temp_id) from temperature WHERE temp_host = '" . $device['device_id'] . "'"), 0) +
           mysql_result(mysql_query("select count(*) from cempMemPool WHERE device_id = '" . $device['device_id'] . "'"), 0) +
           mysql_result(mysql_query("select count(*) from cpmCPU WHERE device_id = '" . $device['device_id'] . "'"), 0);

if($health) {
  echo("
<li class=" . $select['health'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/health/'>
    <img src='images/16/chart_curve.png' align=absmiddle border=0> Health
  </a>
</li>
");
}

$cisco_sensors = mysql_result(mysql_query("SELECT count(*) FROM `entPhysical` WHERE device_id = '".$device['device_id']."' AND entSensorType != '' AND entSensorType NOT LIKE 'No%'"),0);

if($cisco_sensors) {
  echo("
<li class=" . $select['ciscosensors'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/ciscosensors/'>
    <img src='images/16/contrast.png' align=absmiddle border=0> Sensors
  </a>
</li>
");
}

if(is_dir($config['collectd_dir'] . "/" . $device['hostname'] ."/")) {
  echo("
<li class=" . $select['collectd'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/collectd/'>
    <img src='images/16/chart_line.png' align=absmiddle border=0> CollectD
  </a>
</li>
");
}

if(@mysql_result(mysql_query("select count(interface_id) from interfaces WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') {
  echo("
<li class=" . $select['ports'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/ports/".$config['ports_page_default']."'>
    <img src='images/16/connect.png' align=absmiddle border=0> Ports
  </a>
</li>");
}

if(@mysql_result(mysql_query("select count(vlan_id) from vlans WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') {
  echo("
<li class=" . $select['vlans'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vlans/'>
    <img src='images/16/vlans.png' align='absmiddle' border='0'> VLANs
  </a>
</li>");
}

if(@mysql_result(mysql_query("select count(*) from vrfs WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') {
  echo("
<li class=" . $select['vrfs'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/vrfs/'>
    <img src='images/16/layers.png' align=absmiddle border=0> VRFs
  </a>
</li>");
}


if($config['enable_bgp'] && $device['bgpLocalAs']) {
  echo("
<li class=" . $select['bgp'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/bgp/'>
    <img src='images/16/link.png' align=absmiddle border=0> BGP
  </a>
</li>");
}

if(@mysql_result(mysql_query("SELECT count(*) FROM nagios_hosts WHERE address = '".$device['hostname']."'", $nagios_link), 0) > '0') {
  echo("
<li class=" . $select['nagios'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/nagios/'>
    <img src='images/16/transmit_blue.png' align=absmiddle border=0> Nagios
  </a>
</li>");
}


if($_SESSION[userlevel] >= "5" && mysql_result(mysql_query("SELECT count(*) FROM links AS L, interfaces AS I WHERE I.device_id = '".$device['device_id']."' AND I.interface_id = L.src_if"),0)) {
  echo("
<li class=" . $select['map'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/map/'>
    <img src='images/16/chart_organisation.png' align=absmiddle border=0> Map
  </a>
</li>
");
}

if($config['enable_inventory'] && @mysql_result(mysql_query("SELECT * FROM `entPhysical` WHERE device_id = '".$_GET['id']."'"), 0) > '0') {

  echo("<li class=" . $select['entphysical'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/entphysical/'>
    <img src='images/16/bricks.png' align=absmiddle border=0> Inventory
  </a>
</li>
");


}

#if(mysql_result(mysql_query("select count(storage_id) from storage WHERE host_id = '" . $device['device_id'] . "'"), 0) > '0') {
#  echo("
#<li class=" . $select['storage'] . ">
#  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/storage/'>
#    <img src='images/16/database.png' align=absmiddle border=0> Storage
#  </a>
#</li>
#");
#}


if(mysql_result(mysql_query("select count(service_id) from services WHERE service_host = '" . $device['device_id'] . "'"), 0) > '0') {
  echo("
<li class=" . $select['srv'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/srv/'>
    <img src='images/16/server_cog.png' align=absmiddle border=0> Services
  </a>
</li>
");
}

echo("
<li class=" . $select['events'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/events/'>
    <img src='images/16/report_magnify.png' align=absmiddle border=0> Events
  </a>
</li>");

if($config['enable_syslog']) { echo("
<li class=" . $select['syslog'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/syslog/'>
    <img src='images/16/printer.png' align=absmiddle border=0> Syslog
  </a>
</li>
"); }

if($_SESSION[userlevel] >= "5" && is_file($config['rancid_configs'] . $device['hostname'])) {
  echo("
<li class=" . $select['showconfig'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/showconfig/'>
    <img src='images/16/page_white_text.png' align=absmiddle border=0> Config
  </a>
</li>
");
}

if($_SESSION[userlevel] >= "5") {
  echo("
<li class=" . $select['edit'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/edit/'>
    <img src='images/16/server_edit.png' align=absmiddle border=0> Settings
  </a>
</li>
");
}


echo("</ul>");
echo("
<div class=contentstyle>");

include("pages/device/".mres($section).".inc.php");


echo("</div>
");
}

} else { echo("<span class=alert>You do not have the necessary access permissions to view this device.</span>"); }
?>

