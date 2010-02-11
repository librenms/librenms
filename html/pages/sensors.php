<?php

$datas = array('Temperatures', 'Voltages', 'Fanpeeds');

print_optionbar_start();

unset ($sep);
foreach ($datas as $texttype) {
  $type = strtolower($texttype);
  if (!$_GET['opta']) { $_GET['opta'] = $type; }
  echo($sep);
  if ($_GET['opta'] == $type) { echo("<strong>"); }
  echo("<a href='".$config['base_url']."/sensors/" . $type . "/'> " . $texttype ."</a>\n");
  if ($_GET['opta'] == $type) { echo("</strong>"); }
  $sep = ' | ';
}
unset ($sep);

print_optionbar_end();



switch ($_GET['opta'])
{
  case 'temperatures':
  case 'voltages':
  case 'fanspeeds':
    include('pages/sensors/'.$_GET['opta'].'.php');
    break;
  default:
    include('pages/sensors/temperatures.php');
    break;
}

?>
