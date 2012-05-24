<?php

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'routing',
                    'proto'   => 'ipsec_tunnels');

print_optionbar_start();

echo("<span style='font-weight: bold;'>IPSEC Tunnels</span> &#187; ");

$menu_options = array('basic' => 'Basic',
                      );

if(!isset($vars['view'])) { $vars['view'] = "basic"; }

echo("<span style='font-weight: bold;'>VRFs</span> &#187; ");

$menu_options = array('basic' => 'Basic',
#                      'detail' => 'Detail',
                      );

if (!$_GET['opta']) { $_GET['opta'] = "basic"; }

$sep = "";
foreach ($menu_options as $option => $text)
{
  if ($vars['view'] == $option) { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link($text, $link_array,array('view'=>$option)));
  if ($vars['view'] == $option) { echo("</span>"); }
  echo(" | ");
}

echo(' Graphs: ');

$graph_types = array("bits" => "Bits",
                     "pkts" => "Packets");

foreach ($graph_types as $type => $descr)
{
  echo("$type_sep");
  if ($vars['graph'] == $type) { echo("<span class='pagemenu-selected'>"); }
  echo(generate_link($descr, $link_array,array('view'=>'graphs','graph'=>$type)));
  if ($vars['graph'] == $type) { echo("</span>"); }

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
  if (isset($vars['graph']))
  {
    echo('<tr class="list-bold">');
    echo("<td colspan = 3>");
    $graph_type = "ipsectunnel_" . $vars['graph'];

    $graph_array['height'] = "100";
    $graph_array['width']  = "215";
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $tunnel['tunnel_id'];
    $graph_array['type']   = $graph_type;

    include("includes/print-graphrow.inc.php");

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
