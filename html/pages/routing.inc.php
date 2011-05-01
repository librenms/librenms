<?php

if ($_GET['optb'] == "graphs" || $_GET['optc'] == "graphs") { $graphs = "graphs"; } else { $graphs = "nographs"; }

#$datas[] = 'overview';

### $routing_count is populated by print-menubar.inc.php

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

  echo("<a href='routing/" . $type . "/'> " . $type_text[$type] ." (".$routing_count[$type].")</a>");
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
