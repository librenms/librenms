<?php

if ($_GET['optb'] == "graphs" || $_GET['optc'] == "graphs") { $graphs = "graphs"; } else { $graphs = "nographs"; }

#$datas[] = 'overview';

$routing_count['bgp'] = mysql_result(mysql_query("select count(*) from bgpPeers"), 0);
if ($routing_count['bgp']) { $datas[] = 'bgp'; }

$routing_count['ospf'] = mysql_result(mysql_query("select count(*) from ospf_ports"), 0);
if ($routing_count['ospf']) { $datas[] = 'ospf'; }

$routing_count['cef'] = mysql_result(mysql_query("select count(*) from cef_switching"), 0);
if ($routing_count['cef']) { $datas[] = 'cef'; }

$routing_count['vrf'] = @mysql_result(mysql_query("select count(*) from vrfs"), 0);
if($routing_count['vrf']) { $datas[] = 'vrf'; }

#$type_text['overview'] = "Overview";
$type_text['bgp'] = "BGP";
$type_text['cef'] = "CEF";
$type_text['ospf'] = "OSPF";
$type_text['vrf'] = "VRFs";

print_optionbar_start();

#if (!$_GET['opta']) { $_GET['opta'] = "overview"; }

echo("<span style='font-weight: bold;'>Routing</span> &#187; ");

unset($sep);
foreach ($datas as $type)
{

  if (!$_GET['opta']) { $_GET['opta'] = $type; }

  echo($sep);

  if ($_GET['opta'] == $type)
  {
    echo('<span class="pagemenu-selected">');
  }

  echo("<a href='routing/" . $type . ($_GET['optb'] ? "/" . $_GET['optb'] : ''). "/'> " . $type_text[$type] ." (".$routing_count[$type].")</a>");
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
