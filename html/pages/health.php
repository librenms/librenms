<?php

$datas = array('Processors','Memory','Storage','Temperatures', 'Voltages', 'Fanspeeds', 'Frequencies', 'Current');

if(!$_GET['opta']) { $_GET['opta'] = "processors"; }

print_optionbar_start();

$sep = "";
foreach ($datas as $texttype) {
  $type = strtolower($texttype);
  if (!isset($_GET['opta'])) { $_GET['opta'] = $type; }
  echo($sep);
  if ($_GET['opta'] == $type) { echo("<strong>"); }
  echo("<a href='".$config['base_url']."/health/" . $type . "/'> " . $texttype ."</a>\n");
  if ($_GET['opta'] == $type) { echo("</strong>"); }
  $sep = ' | ';
}
unset ($sep);

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
