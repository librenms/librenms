<?php

$temperatures = mysql_result(mysql_query("select count(*) from temperature WHERE device_id = '" . $device['device_id'] . "'"), 0);
$storage = mysql_result(mysql_query("select count(*) from storage WHERE device_id = '" . $device['device_id'] . "'"), 0);
$memory = mysql_result(mysql_query("select count(*) from mempools WHERE device_id = '" . $device['device_id'] . "'"), 0);
$processor  = mysql_result(mysql_query("select count(*) from processors WHERE device_id = '" . $device['device_id'] . "'"), 0);
$fans = mysql_result(mysql_query("select count(*) from fanspeed WHERE device_id = '" . $device['device_id'] . "'"), 0);
$volts = mysql_result(mysql_query("select count(*) from voltage WHERE device_id = '" . $device['device_id'] . "'"), 0);
$freqs = mysql_result(mysql_query("select count(*) from frequency WHERE device_id = '" . $device['device_id'] . "'"), 0);
$current = mysql_result(mysql_query("select count(*) from current WHERE device_id = '" . $device['device_id'] . "'"), 0);

if ($processor) { $datas[] = 'processors'; }
if ($memory) { $datas[] = 'memory'; }
if ($storage) { $datas[] = 'storage'; }
if ($temperatures) { $datas[] = 'temperatures'; }
if ($fans) { $datas[] = 'fanspeeds'; }
if ($volts) { $datas[] = 'voltages'; }
if ($freqs) { $datas[] = 'frequencies'; }
if ($current) { $datas[] = 'current'; }

$type_text['temperatures'] = "Temperatures";
$type_text['memory'] = "Memory Pools";
$type_text['storage'] = "Disk Usage";
$type_text['processors'] = "Processor Usage";
$type_text['voltages'] = "Voltages";
$type_text['fanspeeds'] = "Fan Speeds";
$type_text['frequencies'] = "Frequencies";
$type_text['current'] = "Current";

print_optionbar_start();

if(!$_GET['opta']) { echo("<b>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/health/'>Main</a>\n");
if(!$_GET['opta']) { echo("</b>"); }


foreach ($datas as $type) {
  if ($_GET['opta'] == $type) { echo("<strong>"); }
  echo(" | <a href='".$config['base_url']."/device/" . $device['device_id'] . "/health/" . $type . "/'>" . $type_text[$type] ."</a>\n");
  if ($_GET['opta'] == $type) { echo("</strong>"); }
}

print_optionbar_end();

if (is_file("pages/device/health/".mres($_GET['opta']).".inc.php")) 
{ 
   include("pages/device/health/".mres($_GET['opta']).".inc.php"); 
} else { 
  foreach ($datas as $type) {
    $graph_title = $type_text[$type];
    $graph_type = "device_".$type;
    include ("includes/print-device-graph.php"); 
  }
}


?>
