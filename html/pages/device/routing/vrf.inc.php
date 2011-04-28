<?php

print_optionbar_start();

$menu_options = array('basic' => 'Basic',
                      );

if (!$_GET['opta']) { $_GET['opta'] = "basic"; }

$sep = "";
foreach ($menu_options as $option => $text)
{
  echo($sep);
  if ($_GET['opta'] == $option) { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="device/' . $device['device_id'] . '/routing/vrf/' . $option . ($_GET['optc'] ? '/' . $_GET['optc'] : ''). '/">' . $text
 . '</a>');
  if ($_GET['opta'] == $option) { echo("</span>"); }
  $sep = " | ";
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
$vrf_query = mysql_query("select * from vrfs WHERE device_id = '".$device['device_id']."' ORDER BY 'vrf_name'");
while ($vrf = mysql_fetch_assoc($vrf_query))
{
  include("includes/print-vrf.inc.php");
  $i++;
}

echo("</table></div>");

?>
