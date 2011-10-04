<?php

print_optionbar_start();

echo("<span style='font-weight: bold;'>IPSEC Tunnels</span> &#187; ");

$menu_options = array('basic' => 'Basic',
                      );

if (!$_GET['opta']) { $_GET['opta'] = "basic"; }

$sep = "";
foreach ($menu_options as $option => $text)
{
  if ($_GET['optd'] == $option) { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="device/' . $device['device_id'] . '/routing/ipsec_tunnels/' . $option . '/">' . $text
 . '</a>');
  if ($_GET['optd'] == $option) { echo("</span>"); }
  echo(" | ");
}

unset($sep);

echo(' Graphs: ');

$graph_types = array("bits"   => "Bits",
                     "pkts"   => "Packets",
                     "errors" => "Errors");

foreach ($graph_types as $type => $descr)
{
  echo("$type_sep");
  if ($_GET['opte'] == $type) { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="device/' . $device['device_id'] . '/routing/ipsec_tunnels/graphs/'.$type.'/">'.$descr.'</a>');
  if ($_GET['opte'] == $type) { echo("</span>"); }

#  echo('(');
#  if ($_GET['opte'] == $type) { echo("<span class='pagemenu-selected'>"); }
#  echo('<a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/ipsec_tunnelss/'.$type.'/thumbs/">Mini</a>');
#  if ($_GET['opte'] == $type) { echo("</span>"); }
#  echo(')');
  $type_sep = " | ";
}

print_optionbar_end();

echo("<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=0 width=100%>");
$i = "0";
foreach (dbFetchRows("SELECT * FROM `ipsec_tunnels` WHERE `device_id` = ? ORDER BY `peer_addr`", array($device['device_id'])) as $tunnel)
{

if (is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }

if($tunnel['tunnel_status'] == "active") { $tunnel_class="green"; } else { $tunnel_class="red"; }

echo("<tr bgcolor='$bg_colour'>");
echo("<td width=320 class=list-large>" . $tunnel['local_addr'] . "  &#187;  " . $tunnel['peer_addr'] . "</a></td>");
echo("<td width=150 class=box-desc>" . $tunnel['tunnel_name'] . "</td>");
echo("<td width=100 class=list-large><span class='".$tunnel_class."'>" . $tunnel['tunnel_status'] . "</span></td>");
echo("</tr>");
  if ($_GET['optd'] == "graphs")
  {
    echo('<tr class="list-bold">');
    echo("<td colspan = 3>");
    $graph_type = "ipsectunnel_" . $_GET['opte'];

$graph_array['height'] = "100";
$graph_array['width']  = "215";
$graph_array['to']     = $config['time']['now'];
$graph_array['id']     = $tunnel['tunnel_id'];
$graph_array['type']   = $graph_type;

include("includes/print-quadgraphs.inc.php");

   echo("
   </td>
   </tr>");
  }

echo("</td>");
echo("</tr>");

  $i++;
}

echo("</table></div>");

?>
