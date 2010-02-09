<?php

switch ($_GET['opta'])
{
  case 'temperatures':
  case 'voltages':
  case 'fans':
    include('pages/sensors/'.$_GET['opta'].'.php');
    break;
  default:
    include('pages/sensors/temperatures.php');
    break;
}

?>