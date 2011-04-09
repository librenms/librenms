<?php

$storage = mysql_result(mysql_query("select count(*) from storage WHERE device_id = '" . $device['device_id'] . "'"), 0);
$diskio = mysql_result(mysql_query("select count(*) from ucd_diskio WHERE device_id = '" . $device['device_id'] . "'"), 0);
$mempools = mysql_result(mysql_query("select count(*) from mempools WHERE device_id = '" . $device['device_id'] . "'"), 0);
$processor  = mysql_result(mysql_query("select count(*) from processors WHERE device_id = '" . $device['device_id'] . "'"), 0);

$temperatures = mysql_result(mysql_query("select count(*) from sensors WHERE sensor_class='temperature' AND device_id = '" . $device['device_id'] . "'"), 0);
$humidity = mysql_result(mysql_query("select count(*) from sensors WHERE sensor_class='humidity' AND device_id = '" . $device['device_id'] . "'"), 0);
$fans = mysql_result(mysql_query("select count(*) from sensors WHERE sensor_class='fanspeed' AND device_id = '" . $device['device_id'] . "'"), 0);
$volts = mysql_result(mysql_query("select count(*) from sensors WHERE sensor_class='voltage' AND device_id = '" . $device['device_id'] . "'"), 0);
$current = mysql_result(mysql_query("select count(*) from sensors WHERE sensor_class='current' AND device_id = '" . $device['device_id'] . "'"), 0);
$freqs = mysql_result(mysql_query("select count(*) from sensors WHERE sensor_class='freq' AND device_id = '" . $device['device_id'] . "'"), 0);

$datas[] = 'overview';
if ($processor) { $datas[] = 'processor'; }
if ($mempools) { $datas[] = 'mempool'; }
if ($storage) { $datas[] = 'storage'; }
if ($diskio) { $datas[] = 'diskio'; }
if ($temperatures) { $datas[] = 'temperature'; }
if ($humidity) { $datas[] = 'humidity'; }
if ($fans) { $datas[] = 'fanspeed'; }
if ($volts) { $datas[] = 'voltage'; }
if ($freqs) { $datas[] = 'frequency'; }
if ($current) { $datas[] = 'current'; }

$type_text['overview'] = "Overview";
$type_text['temperature'] = "Temperature";
$type_text['humidity'] = "Humidity";
$type_text['mempool'] = "Memory";
$type_text['storage'] = "Disk Usage";
$type_text['diskio'] = "Disk I/O";
$type_text['processor'] = "Processor";
$type_text['voltage'] = "Voltage";
$type_text['fanspeed'] = "Fanspeed";
$type_text['frequency'] = "Frequency";
$type_text['current'] = "Current";

print_optionbar_start();

if (!$_GET['opta']) { $_GET['opta'] = "overview"; }

unset($sep);
foreach ($datas as $type)
{
  echo($sep);

  if ($_GET['opta'] == $type)
  {
    echo('<span class="pagemenu-selected">');
  }

  echo("<a href='".$config['base_url']."/device/".$device['device_id']."/health/" . $type . ($_GET['optb'] ? "/" . $_GET['optb'] : ''). "/'> " . $type_text[$type] ."</a>");
  if ($_GET['opta'] == $type) { echo("</span>"); }
  $sep = " | ";
}

print_optionbar_end();

if (is_file("pages/device/health/".mres($_GET['opta']).".inc.php"))
{
   include("pages/device/health/".mres($_GET['opta']).".inc.php");
} else {
  foreach ($datas as $type)
  {
    if ($type != "overview")
    {
      $graph_title = $type_text[$type];
      $graph_type = "device_".$type;
      include("includes/print-device-graph.php");
    }
  }
}

?>
