<?php

if (!$_GET['opta']) { $_GET['opta'] = "overview"; }
if ($_GET['optb'] == "graphs" || $_GET['optc'] == "graphs") { $graphs = "graphs"; } else { $graphs = "nographs"; }

$datas[] = 'overview';

$vrf_count = @mysql_result(mysql_query("SELECT COUNT(*) FROM `vrfs`"), 0);
if($vrf_count) {  $datas[] = 'vrf'; }

$bgp_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `bgpPeers`"), 0);
if ($bgp_count) { $datas[] = 'bgp'; }

$cef_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `cef_switching`"), 0);
if ($cef_count) { $datas[] = 'cef'; }

$ospf_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `ospf_instances`"), 0);
if ($ospf_count) { $datas[] = 'ospf'; }

$type_text['overview'] = "Overview";
$type_text['bgp'] = "BGP";
$type_text['cef'] = "CEF";
$type_text['ospf'] = "OSPF";
$type_text['vrf'] = "VRFs";

print_optionbar_start();

if (!$_GET['opta']) { $_GET['opta'] = "overview"; }

echo("<span style='font-weight: bold;'>Routing</span> &#187; ");

unset($sep);
foreach ($datas as $type)
{
  echo($sep);

  if ($_GET['opta'] == $type)
  {
    echo('<span class="pagemenu-selected">');
  }

  echo("<a href='routing/" . $type . ($_GET['optb'] ? "/" . $_GET['optb'] : ''). "/'> " . $type_text[$type] ."</a>");
  if ($_GET['opta'] == $type) { echo("</span>"); }
  $sep = " | ";
}

print_optionbar_end();

switch ($_GET['opta'])
{
  case 'overview':
  case 'bgp':
  case 'vrf':
  case 'cef':
  case 'ospf':
    include('pages/routing/'.$_GET['opta'].'.inc.php');
    break;
  default:
    echo("<h2>Error. Please report this to observium developers.</h2>");
    break;
}

?>
