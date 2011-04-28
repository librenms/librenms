<?php

if (!$_GET['opta']) { $_GET['opta'] = "overview"; }
if ($_GET['optb'] == "graphs" || $_GET['optc'] == "graphs") { $graphs = "graphs"; } else { $graphs = "nographs"; }

print_optionbar_start('', '');

  echo('<span style="font-weight: bold;">Routing</span> &#187; ');

  if ($_GET['opta'] == "overview") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/overview/'.$graphs.'/">Overview</a>');
  if ($_GET['opta'] == "overview") { echo("</span>"); }

  echo(" | ");

  ## Start BGP Menu -- FIXME only show if BGP enabled?
  if ($_GET['opta'] == "bgp") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/all/'.$graphs.'/">BGP</a>');
  if ($_GET['opta'] == "bgp") { echo("</span>"); }

  echo(" | ");

  ## Start OSPF Menu -- FIXME only show if BGP enabled?
  if ($_GET['opta'] == "ospf") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/ospf/all/'.$graphs.'/">OSPF</a>');
  if ($_GET['opta'] == "ospf") { echo("</span>"); }

  echo(" | ");

  ## Start VRF Menu -- FIXME only show if BGP enabled?
  if ($_GET['opta'] == "vrf") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/vrf/all/'.$graphs.'/">VRF</a>');
  if ($_GET['opta'] == "vrf") { echo("</span>"); }



print_optionbar_end('', '');

switch ($_GET['opta'])
{
  case 'overview':
  case 'bgp':
  case 'vrf':
    include('pages/routing/'.$_GET['opta'].'.inc.php');
    break;
  default:
    echo("<h2>Error. Please report this to observium developers.</h2>");
    break;
}

?>
