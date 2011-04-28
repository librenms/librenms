<?php


$datas[] = 'overview';

if (@mysql_result(mysql_query("select count(*) from vrfs WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0') 
{
  $datas[] = 'vrf';
}

$bgp_count = mysql_result(mysql_query("select count(*) from bgpPeers WHERE device_id = '" . $device['device_id'] . "'"), 0);
if ($bgp_count) { $datas[] = 'bgp'; }

$cef_count = mysql_result(mysql_query("select count(*) from cef_switching WHERE device_id = '" . $device['device_id'] . "'"), 0);
if ($cef_count) { $datas[] = 'cef'; }

$ospf_count = mysql_result(mysql_query("select count(*) from ospf_instances WHERE device_id = '" . $device['device_id'] . "'"), 0);
if ($ospf_count) { $datas[] = 'ospf'; }

$type_text['overview'] = "Overview";
$type_text['bgp'] = "BGP";
$type_text['cef'] = "CEF";
$type_text['ospf'] = "OSPF";
$type_text['vrf'] = "VRFs";

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

  echo("<a href='".$config['base_url']."/device/".$device['device_id']."/routing/" . $type . ($_GET['optb'] ? "/" . $_GET['optb'] : ''). "/'> " . $type_text[$type] ."</a>");
  if ($_GET['opta'] == $type) { echo("</span>"); }
  $sep = " | ";
}

print_optionbar_end();

if (is_file("pages/device/routing/".mres($_GET['opta']).".inc.php"))
{
   include("pages/device/routing/".mres($_GET['opta']).".inc.php");
} else {
  foreach ($datas as $type)
  {
    if ($type != "overview")
    {
      if(is_file("pages/device/routing/overview/".mres($type).".inc.php")) {

        $g_i++;
        if (!is_integer($g_i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

        echo('<div style="background-color: '.$row_colour.';">');
        echo('<div style="padding:4px 0px 0px 8px;"><span class=graphhead>'.$type_text[$type].'</span>');
        include("pages/device/routing/overview/".mres($type).".inc.php");
        echo('</div>');
        echo('</div>');
      } else {
        $graph_title = $type_text[$type];
        $graph_type = "device_".$type;
        include("includes/print-device-graph.php");
      }
    }
  }
}

?>
