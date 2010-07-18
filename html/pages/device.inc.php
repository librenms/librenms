<?php

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

  if($config['os'][$device['os']]['group']) {$device['os_group'] = $config['os'][$device['os']]['group']; }

  echo('<table cellpadding="15" cellspacing="0" class="devicetable" width="100%">');
  include("includes/device-header.inc.php");
  echo("</table>");

echo("<div class=mainpane>");

echo('<ul id="maintab" class="shadetabs">');

if($config['show_overview_tab']) {
echo("
<li class=" . $select['overview'] . ">
  <a href='".$config['base_url']."/device/" . $device['device_id'] . "/overview/'>
    <img src='images/16/server_lightning.png' align=absmiddle border=0> Overview
  </a>
</li>");
}


#if ((is_file($config['install_dir'] . "/html/pages/device/graphs/os-".$device['os'].".inc.php")) ||
#   ($device['os_group'] && is_file($config['install_dir'] . "/html/pages/device/graphs/os-".$device['os_group'].".inc.php")))
#{
  echo('<li class="' . $select['graphs'] . '">
  <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/graphs/">
    <img src="images/16/server_chart.png" align="absmiddle" border="0"> Graphs
  </a>
</li>');
#}

$health =  mysql_result(mysql_query("select count(*) from storage WHERE device_id = '" . $device['device_id'] . "'"), 0) +
           mysql_result(mysql_query("select count(sensor_id) from sensors WHERE device_id = '" . $device['device_id'] . "'"), 0) +
           mysql_result(mysql_query("select count(*) from cempMemPool WHERE device_id = '" . $device['device_id'] . "'"), 0) +
           mysql_result(mysql_query("select count(*) from cpmCPU WHERE device_id = '" . $device['device_id'] . "'"), 0) +
	   mysql_result(mysql_query("select count(*) from processors WHERE device_id = '" . $device['device_id'] . "'"), 0) +
	   mysql_result(mysql_query("select count(current_id) from current WHERE device_id = '" . $device['device_id'] . "'"), 0) +
	   mysql_result(mysql_query("select count(freq_id) from frequencies WHERE device_id = '" . $device['device_id'] . "'"), 0) +
	   mysql_result(mysql_query("select count(volt_id) from voltage WHERE device_id = '" . $device['device_id'] . "'"), 0) +
	   mysql_result(mysql_query("select count(fan_id) from fanspeed WHERE device_id = '" . $device['device_id'] . "'"), 0);

if($health) {
  echo('<li class="' . $select['health'] . '">
  <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/health/">
    <img src="images/icons/sensors.png" align="absmiddle" border="0" /> Health
  </a>
</li>');
}

if(@mysql_result(mysql_query("select count(app_id) from applications WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') 
{
  echo('<li class="' . $select['apps'] . '">
  <a href="' . $config['base_url'] . '/device/' . $device['device_id'] . '/apps/">
    <img src="images/icons/apps.png" align="absmiddle" border="0" /> Apps
  </a>
</li>');
}

### This needs to die, rolled into generic sensors! (still need to implement booleans, tx/rx powers and currents)

#$cisco_sensors = mysql_result(mysql_query("SELECT count(*) FROM `entPhysical` WHERE device_id = '".$device['device_id']."' AND entSensorType != '' AND entSensorType NOT LIKE 'No%'"),0);
#if($cisco_sensors) {
#  echo('<li class="' . $select['ciscosensors'] . '">
#  <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/ciscosensors/">
#    <img src="images/16/contrast.png" align="absmiddle" border="0" /> Sensors
#  </a>
#</li>');
#}

if(is_dir($config['collectd_dir'] . "/" . $device['hostname'] ."/")) {
  echo('<li class="' . $select['collectd'] . '">
  <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/collectd/">
    <img src="images/16/chart_line.png" align="absmiddle" border="0" /> CollectD
  </a>
</li>');
}

if(@mysql_result(mysql_query("select count(interface_id) from ports WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') {
  echo('<li class="' . $select['ports'] . '">
  <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/ports/' .$config['ports_page_default']. '">
    <img src="images/16/connect.png" align="absmiddle" border="0" /> Ports
  </a>
</li>');
}

if(@mysql_result(mysql_query("select count(vlan_id) from vlans WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') {
  echo('<li class="' . $select['vlans'] . '">
  <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/vlans/">
    <img src="images/16/vlans.png" align="absmiddle" border="0" /> VLANs
  </a>
</li>');
}

if(@mysql_result(mysql_query("select count(*) from vrfs WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') {
  echo('<li class="' . $select['vrfs'] . '">
  <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/vrfs/">
    <img src="images/16/layers.png" align="absmiddle" border="0" /> VRFs
  </a>
</li>');
}


if($config['enable_bgp'] && $device['bgpLocalAs']) {
  echo('<li class="' . $select['bgp'] . '">
  <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/bgp/">
    <img src="images/16/link.png" align="absmiddle" border="0" /> BGP
  </a>
</li>');
}

### This probably needs to die? DEATH TO NAGIOS!


#if(@mysql_result(mysql_query("SELECT count(*) FROM nagios_hosts WHERE address = '".$device['hostname']."'", $nagios_link), 0) > '0') {
#  echo('<li class="' . $select['nagios'] . '">
#  <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/nagios/">
#    <img src="images/16/transmit_blue.png" align="absmiddle" border="0" /> Nagios
#  </a>
#</li>');
#}


if($_SESSION['userlevel'] >= "5" && mysql_result(mysql_query("SELECT count(*) FROM links AS L, ports AS I WHERE I.device_id = '".$device['device_id']."' AND I.interface_id = L.local_interface_id"),0)) {
  echo('<li class="' . $select['map'] . '">
  <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/map/">
    <img src="images/16/chart_organisation.png" align="absmiddle" border="0" /> Map
  </a>
</li>');
}

if($config['enable_inventory'] && @mysql_result(mysql_query("SELECT * FROM `entPhysical` WHERE device_id = '".$_GET['id']."'"), 0) > '0') {
  echo('<li class="' . $select['entphysical'] . '">
  <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/entphysical/">
    <img src="images/16/bricks.png" align="absmiddle" border="0" /> Inventory
  </a>
</li>');
} elseif ( $config['enable_inventory'] && @mysql_result(mysql_query("SELECT * FROM `hrDevice` WHERE device_id = '".$_GET['id']."'"), 0) > '0') {
  echo('<li class="' . $select['hrdevice'] . '">
  <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/hrdevice/">
    <img src="images/16/bricks.png" align="absmiddle" border="0" /> Inventory
  </a>
</li>');
}



#if(mysql_result(mysql_query("select count(storage_id) from storage WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') {
#  echo("
#<li class=" . $select['storage'] . ">
#  <a href='".$config['base_url']."/device/" . $device['device_id'] . '/storage/">
#    <img src="images/16/database.png" align="absmiddle" border="0" /> Storage
#  </a>
#</li>
#");
#}


if(mysql_result(mysql_query("select count(service_id) from services WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') {
  echo('<li class="' . $select['srv'] . '">
  <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/srv/">
    <img src="images/icons/services.png" align="absmiddle" border="0" /> Services
  </a>
</li>
');
}

if(@mysql_result(mysql_query("select count(toner_id) from toner WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') {
  echo('<li class="' . $select['toner'] . '">
  <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/toner/">
    <img src="images/icons/toner.png" align="absmiddle" border="0" /> Toner
  </a>
</li>');
}


echo('<li class="' . $select['events'] . '">
  <a href="'.$config['base_url']. "/device/" . $device['device_id'] . '/events/">
    <img src="images/16/report_magnify.png" align="absmiddle" border="0" /> Events
  </a>
</li>');

if($config['enable_syslog']) { 
echo('<li class="' . $select['syslog'] . '">
  <a href="'.$config['base_url']."/device/" . $device['device_id'] . '/syslog/">
    <img src="images/16/printer.png" align="absmiddle" border="0" /> Syslog
  </a>
</li>
'); 
}



if($_SESSION['userlevel'] >= "7") {
  if(!is_array($config['rancid_configs'])) { $config['rancid_configs'] = array($config['rancid_configs']); }
  foreach($config['rancid_configs'] as $configs) {
    if ($configs[strlen($configs)-1] != '/') { $configs .= '/'; }
    if(is_file($configs . $device['hostname'])) { $device_config_file = $configs . $device['hostname']; }
  }
}
if($device_config_file) {
  echo('<li class="' . $select['showconfig'] . '">
  <a href="'.$config['base_url']."/device/" . $device['device_id'] . '/showconfig/">
    <img src="images/16/page_white_text.png" align="absmiddle" border="0" /> Config
  </a>
</li>
');
}

if($_SESSION['userlevel'] >= "7") {
  echo('<li class="' . $select['edit'] . '">
  <a href="'.$config['base_url']."/device/" . $device['device_id'] . '/edit/">
    <img src="images/16/server_edit.png" align="absmiddle" border="0" /> Settings
  </a>
</li>
');
}


echo("</ul>");
echo('
<div class="contentstyle">');

include("pages/device/".mres($section).".inc.php");


echo("</div>
");
}

} else { include("includes/error-no-perm-dev.inc.php"); }
?>

