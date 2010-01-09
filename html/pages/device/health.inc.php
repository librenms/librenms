<?php // vim:fenc=utf-8:filetype=php:ts=4

$temp = mysql_result(mysql_query("select count(*) from temperature WHERE temp_host = '" . $device['device_id'] . "'"), 0);
$storage = mysql_result(mysql_query("select count(*) from storage WHERE host_id = '" . $device['device_id'] . "'"), 0);
$cemp = mysql_result(mysql_query("select count(*) from cempMemPool WHERE device_id = '" . $device['device_id'] . "'"), 0);
$cmp = mysql_result(mysql_query("select count(*) from cmpMemPool WHERE device_id = '" . $device['device_id'] . "'"), 0);
$cpm  = mysql_result(mysql_query("select count(*) from cpmCPU WHERE device_id = '" . $device['device_id'] . "'"), 0);
$hrprocessor  = mysql_result(mysql_query("select count(*) from hrDevice WHERE device_id = '" . $device['device_id'] . "' AND `hrDeviceType` = 'hrDeviceProcessor'"), 0);


if ($temp) { $datas[] = 'temp'; }
if ($storage) { $datas[] = 'storage'; }
if ($cemp) { $datas[] = 'cemp'; }
if ($cpm) { $datas[] = 'cpm'; }
if ($cmp) { $datas[] = 'cmp'; }
if ($hrprocessor) { $datas[] = 'hrprocessors'; }

$type_text['temp'] = "Temperatures";
$type_text['cmp'] = "Memory Pools";
$type_text['cemp'] = "Memory Enh Pools";
$type_text['cpm'] = "Processor Usage";
$type_text['storage'] = "Disk Usage";
$type_text['hrprocessors'] = "Processor Usage";


/*
 echo("<div style='margin:auto; text-align: center; margin-top: 0px; margin-bottom: 10px;'>
  <b class='rounded'>
  <b class='rounded1'></b>
  <b class='rounded2'></b>
  <b class='rounded3'></b>
  <b class='rounded4'></b>
  <b class='rounded5'></b></b>
  <div class='roundedfg' style='padding: 0px 5px;'>
  <div style='margin: auto; text-align: left; padding: 2px 5px; padding-left: 11px; clear: both; display:block; height:20px;'>
");
*/

print_optionbar_start();

unset ($sep);
foreach ($datas as $type) {
  if (!$_GET['opta']) { $_GET['opta'] = $type; }
  echo($sep);
  if ($_GET['opta'] == $type) { echo("<strong>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/health/" . $type . "/'>" . $type_text[$type] ."</a>\n");
  if ($_GET['opta'] == $type) { echo("</strong>"); }
  $sep = ' | ';
}
unset ($sep);

print_optionbar_end();

if (is_file("pages/device/health/".mres($_GET['opta']).".inc.php")) { include("pages/device/health/".mres($_GET['opta']).".inc.php"); }


?>
