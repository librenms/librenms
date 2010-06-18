<?php

$datas = array('Processors','Memory','Storage','Temperatures', 'Fanspeeds', 'Voltages', 'Frequencies', 'Current');

if(!$_GET['opta']) { $_GET['opta'] = "processors"; }
if(!$_GET['optb']) { $_GET['optb'] = "nographs"; }


print_optionbar_start('', '');

$sep = "";
foreach ($datas as $texttype) 
{
  $type = strtolower($texttype);
  echo($sep);
  if ($_GET['opta'] == $type) 
  { 
    echo("<strong>");
    echo('<img src="images/icons/'.$type.'.png" class="optionicon" />');
  } 
  else 
  {
    echo('<img src="images/icons/greyscale/'.$type.'.png" class="optionicon" />');
  }
  echo('<a href="'.$config['base_url'].'/health/' . $type . ($_GET['optb'] ? '/' . $_GET['optb'] : ''). '/"> ' . $texttype ."</a>\n");
  if ($_GET['opta'] == $type) { echo("</strong>"); }
  $sep = ' | ';
}
unset ($sep);

echo('<div style="float: right;">');

if($_GET['optb'] == "graphs") { echo('<strong>');
  echo('<img src="images/icons/graphs.png" class="optionicon" />');
} else {
  echo('<img src="images/icons/greyscale/graphs.png" class="optionicon" />');
}
echo('<a href="health/'. $_GET['opta'].'/graphs/"> Graphs</a>');
if($_GET['optb'] == "graphs") { echo('</strong>'); }
echo(' | ');
if($_GET['optb'] == "nographs") { echo('<strong>');
  echo('<img src="images/icons/nographs.png" class="optionicon" />');
} else {
  echo('<img src="images/icons/greyscale/nographs.png" class="optionicon" />');
}
echo('<a href="health/'. $_GET['opta'].'/nographs/"> No Graphs</a>');
if($_GET['optb'] == "nographs") { echo('</strong>'); }


echo('</div>');

print_optionbar_end();

switch ($_GET['opta'])
{
  case 'processors':
  case 'memory':
  case 'storage':
  case 'temperatures':
  case 'voltages':
  case 'fanspeeds':
  case 'frequencies':
  case 'current':
    include('pages/health/'.$_GET['opta'].'.inc.php');
    break;
  default:
    include('pages/health/temperatures.inc.php');
    break;
}

?>
