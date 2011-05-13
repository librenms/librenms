<?php

print_optionbar_start();

$menu_options = array('basic' => 'Basic',
                      );

if (!$_GET['opta']) { $_GET['opta'] = "basic"; }

$sep = "";
foreach ($menu_options as $option => $text)
{
  if ($_GET['optb'] == $option) { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="device/' . $device['device_id'] . '/routing/vrf/' . $option . '/">' . $text
 . '</a>');
  if ($_GET['optb'] == $option) { echo("</span>"); }
  echo(" | ");
}

unset($sep);

echo(' Graphs: ');

$graph_types = array("bits" => "Bits",
                     "upkts" => "Unicast Packets",
                     "nupkts" => "Non-Unicast Packets",
                     "errors" => "Errors",
                     "etherlike" => "Etherlike");

foreach ($graph_types as $type => $descr)
{
  echo("$type_sep");
  if ($_GET['optc'] == $type) { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="device/' . $device['device_id'] . '/routing/vrf/graphs/'.$type.'/">'.$descr.'</a>');
  if ($_GET['optc'] == $type) { echo("</span>"); }

#  echo('(');
#  if ($_GET['optb'] == $type) { echo("<span class='pagemenu-selected'>"); }
#  echo('<a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/vrfs/'.$type.'/thumbs/">Mini</a>');
#  if ($_GET['optb'] == $type) { echo("</span>"); }
#  echo(')');
  $type_sep = " | ";
}

print_optionbar_end();

echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>");
$i = "0";
foreach (dbFetchRows("SELECT * FROM `vrfs` WHERE `device_id` = ? ORDER BY `vrf_name`", array($device['device_id'])) as $vrf)
{
  include("includes/print-vrf.inc.php");
  $i++;
}

echo("</table></div>");

?>
