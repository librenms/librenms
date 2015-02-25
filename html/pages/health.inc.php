<?php

$datas = array('mempool','processor','storage');
if ($used_sensors['temperature']) $datas[] = 'temperature';
if ($used_sensors['charge']) $datas[] = 'charge';
if ($used_sensors['humidity']) $datas[] = 'humidity';
if ($used_sensors['fanspeed']) $datas[] = 'fanspeed';
if ($used_sensors['voltage']) $datas[] = 'voltage';
if ($used_sensors['frequency']) $datas[] = 'frequency';
if ($used_sensors['current']) $datas[] = 'current';
if ($used_sensors['power']) $datas[] = 'power';
if ($used_sensors['dBm']) $datas[] = 'dBm';

// FIXME generalize -> static-config ?
$type_text['overview'] = "Overview";
$type_text['temperature'] = "Temperature";
$type_text['charge'] = "Battery Charge";
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
$type_text['toner'] = "Toner";
$type_text['dBm'] = "dBm";

if (!$vars['metric']) { $vars['metric'] = "processor"; }
if (!$vars['view']) { $vars['view'] = "detail"; }

$link_array = array('page'    => 'health');

$pagetitle[] = "Health";

print_optionbar_start('', '');

echo('<span style="font-weight: bold;">Health</span> &#187; ');

$sep = "";
foreach ($datas as $texttype)
{
  $metric = strtolower($texttype);
  echo($sep);
  if ($vars['metric'] == $metric)
  {
    echo("<span class='pagemenu-selected'>");
  }

  echo(generate_link($type_text[$metric],$link_array,array('metric'=> $metric, 'view' => $vars['view'])));

  if ($vars['metric'] == $metric) { echo("</span>"); }

  $sep = ' | ';
}

unset ($sep);

echo('<div style="float: right;">');

if ($vars['view'] == "graphs")
{
  echo('<span class="pagemenu-selected">');
}
echo(generate_link("Graphs",$link_array,array('metric'=> $vars['metric'], 'view' => "graphs")));
if ($vars['view'] == "graphs")
{
  echo('</span>');
}

echo(' | ');

if ($vars['view'] != "graphs")
{
  echo('<span class="pagemenu-selected">');
}

echo(generate_link("No Graphs",$link_array,array('metric'=> $vars['metric'], 'view' => "detail")));

if ($vars['view'] != "graphs")
{
  echo('</span>');
}

echo('</div>');

print_optionbar_end();

if (in_array($vars['metric'],array_keys($used_sensors))
  || $vars['metric'] == 'processor'
  || $vars['metric'] == 'storage'
  || $vars['metric'] == 'toner'
  || $vars['metric'] == 'mempool')
{
  include('pages/health/'.$vars['metric'].'.inc.php');
}
else
{
  echo("No sensors of type " . $vars['metric'] . " found.");
}

?>
