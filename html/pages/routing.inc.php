<?php

$datas = array('overview', 'bgp');

$type_text['overview'] = "Overview";
$type_text['bgp'] = "BGP";

if (!$_GET['opta']) { $_GET['opta'] = "overview"; }
if (!$_GET['optb']) { $_GET['optb'] = "nographs"; }

print_optionbar_start('', '');

$sep = "";
foreach ($datas as $texttype)
{
  $type = strtolower($texttype);
  echo($sep);
  if ($_GET['opta'] == $type)
  {
    echo("<span class='pagemenu-selected'>"); 
  }

  echo('<a href="'.$config['base_url'].'/routing/' . $type . ($_GET['optb'] ? '/' . $_GET['optb'] : ''). '/">' . $type_text[$type] ."</a>");
 
 if ($_GET['opta'] == $type) { echo("</span>"); }

  $sep = ' | ';
}

unset ($sep);

echo('<div style="float: right;">');

if ($_GET['optb'] == "graphs")
{
  echo('<span class="pagemenu-selected">');
}

echo('<a href="routing/'. $_GET['opta'].'/graphs/"> Graphs</a>');

if ($_GET['optb'] == "graphs")
{
  echo('</span>');
}

echo(' | ');

if ($_GET['optb'] == "nographs")
{
  echo('<span class="pagemenu-selected">');
}

echo('<a href="routing/'. $_GET['opta'].'/nographs/"> No Graphs</a>');

if ($_GET['optb'] == "nographs") 
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
