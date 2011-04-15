<?php

$datas = array('processor','mempool','storage','temperature', 'humidity', 'fanspeed', 'voltage', 'frequency', 'current');

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

if (!$_GET['opta']) { $_GET['opta'] = "processor"; }
if (!$_GET['optb']) { $_GET['optb'] = "nographs"; }

print_optionbar_start('', '');

$sep = "";
foreach ($datas as $texttype)
{
  $type = strtolower($texttype);
  echo($sep);
  if ($_GET['opta'] == $type)
  {
    echo("<span class='pagemenu-selected'>"); 
  }

  echo('<a href="health/' . $type . ($_GET['optb'] ? '/' . $_GET['optb'] : ''). '/">' . $type_text[$type] .'</a>');
 
  if ($_GET['opta'] == $type) { echo("</span>"); }

  $sep = ' | ';
}

unset ($sep);

echo('<div style="float: right;">');

if ($_GET['optb'] == "graphs")
{
  echo('<span class="pagemenu-selected">');
}

echo('<a href="health/'. $_GET['opta'].'/graphs/"> Graphs</a>');

if ($_GET['optb'] == "graphs")
{
  echo('</span>');
}

echo(' | ');

if ($_GET['optb'] == "nographs")
{
  echo('<span class="pagemenu-selected">');
}

echo('<a href="health/'. $_GET['opta'].'/nographs/"> No Graphs</a>');

if ($_GET['optb'] == "nographs") 
{ 
  echo('</span>'); 
}

echo('</div>');

print_optionbar_end();

switch ($_GET['opta'])
{
  case 'processor':
  case 'mempool':
  case 'storage':
  case 'temperature':
  case 'humidity':
  case 'voltage':
  case 'fanspeed':
  case 'frequency':
  case 'current':
    include('pages/health/'.$_GET['opta'].'.inc.php');
    break;
  default:
    include('pages/health/temperature.inc.php'); # FIXME perhaps an error message instead?
    break;
}

?>
