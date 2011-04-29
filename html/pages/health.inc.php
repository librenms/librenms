<?php

$datas = array('processor','mempool','storage');
if ($used_sensors['temperature']) $datas[] = 'temperature';
if ($used_sensors['humidity']) $datas[] = 'humidity';
if ($used_sensors['fanspeed']) $datas[] = 'fanspeed';
if ($used_sensors['voltage']) $datas[] = 'voltage';
if ($used_sensors['frequency']) $datas[] = 'frequency';
if ($used_sensors['current']) $datas[] = 'current';
if ($used_sensors['power']) $datas[] = 'power';

# FIXME generalize -> static-config ?
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
$type_text['power'] = "Power";

if (!$_GET['opta']) { $_GET['opta'] = "processor"; }
if (!$_GET['optb']) { $_GET['optb'] = "nographs"; }

print_optionbar_start('', '');

echo('<span style="font-weight: bold;">Health</span> &#187; ');

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

if (in_array($_GET['opta'],array_keys($used_sensors)) 
  || $_GET['opta'] == 'processor'
  || $_GET['opta'] == 'storage'
  || $_GET['opta'] == 'mempool')
{
  include('pages/health/'.$_GET['opta'].'.inc.php');
}
else
{
  echo("No sensors of type " . $_GET['opta'] . " found.");
}

?>
