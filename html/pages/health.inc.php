<?php

$datas = array('Processor','Memory','Storage','Temperature', 'Humidity', 'Fanspeed', 'Voltage', 'Frequency', 'Current');

if (!$_GET['opta']) { $_GET['opta'] = "processors"; }
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
 #'style='font-weight: bold; background-color: #ffffff; -moz-border-radius: 15px; border-radius: 15px; padding: 2px 8px;'>");
#    echo('<img src="images/icons/'.$type.'.png" class="optionicon" />');
  }
  else
  {
#    echo('<img src="images/icons/greyscale/'.$type.'.png" class="optionicon" />');
  }
  echo('<a href="'.$config['base_url'].'/health/' . $type . ($_GET['optb'] ? '/' . $_GET['optb'] : ''). '/">' . $texttype ."</a>");
 
 if ($_GET['opta'] == $type) { echo("</span>"); }

  $sep = ' | ';
}

unset ($sep);

echo('<div style="float: right;">');

if ($_GET['optb'] == "graphs")
{
  echo('<span class="pagemenu-selected">');
}

echo('<a href="health/'. $_GET['opta'].'/graphs/"> Graphs</a>');

if ($_GET['optb'] == "graphs")
{
  echo('</span>');
}

echo(' | ');

if ($_GET['optb'] == "nographs")
{
  echo('<span class="pagemenu-selected">');
}

echo('<a href="health/'. $_GET['opta'].'/nographs/"> No Graphs</a>');

if ($_GET['optb'] == "nographs") 
{ 
  echo('</span>'); 
}

echo('</div>');

print_optionbar_end();

switch ($_GET['opta'])
{
  case 'processor':
  case 'memory':
  case 'storage':
  case 'temperature':
  case 'humidity':
  case 'voltage':
  case 'fanspeed':
  case 'frequency':
  case 'current':
    include('pages/health/'.$_GET['opta'].'.inc.php');
    break;
  default:
    include('pages/health/temperature.inc.php');
    break;
}

?>
