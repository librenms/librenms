<?php

if (!$_GET['opta']) { $_GET['opta'] = "overview"; }
if ($_GET['optb'] == "graphs" || $_GET['optc'] == "graphs") { $graphs = "graphs"; } else { $graphs = "nographs"; }

print_optionbar_start('', '');

  if ($_GET['opta'] == "overview") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/overview/'.$graphs.'/">Overview</a>');
  if ($_GET['opta'] == "overview") { echo("</span>"); }

  echo(" | ");

  ## Start BGP Menu -- FIXME only show if BGP enabled?
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "all") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/all/'.$graphs.'/">BGP</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "all") { echo("</span>"); }
  echo('(');
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "internal") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/internal/'.$graphs.'/">Internal</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "internal") { echo("</span>"); }
  echo("|");
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "external") { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="routing/bgp/external/'.$graphs.'/">External</a>');
  if ($_GET['opta'] == "bgp" && $_GET['optb'] == "external") { echo("</span>"); }
  echo(')');
  ## End BGP Menu


if(!isset($graphs)) { $graphs == "nographs"; }

echo('<div style="float: right;">');

if ($graphs == "graphs")
{
  echo('<span class="pagemenu-selected">');
}

if(isset($_GET['optc']))
{
  echo('<a href="routing/'. $_GET['opta'].'/graphs/"> Graphs</a>');
} else {
  echo('<a href="routing/'. $_GET['opta'].'/'.$_GET['optb'].'/graphs/"> Graphs</a>');
}

if ($graphs == "graphs")
{
  echo('</span>');
}

echo(' | ');

if ($graphs == "nographs")
{
  echo('<span class="pagemenu-selected">');
}

if(isset($_GET['optc']))
{
  echo('<a href="routing/'. $_GET['opta'].'/nographs/"> No Graphs</a>');
} else {
  echo('<a href="routing/'. $_GET['opta'].'/'.$_GET['optb'].'/nographs/"> No Graphs</a>');
}

if ($graphs == "nographs") 
{ 
  echo('</span>'); 
}

echo('</div>');

print_optionbar_end();

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
