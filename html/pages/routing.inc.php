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


print_optionbar_end('', '');

switch ($_GET['opta'])
{
  case 'overview':
  case 'bgp':
    include('pages/routing/'.$_GET['opta'].'.inc.php');
    break;
  default:
    echo("<h2>Error. Please report this to observium developers.</h2>");
    break;
}

?>
