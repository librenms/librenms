<?php

if ($_SESSION['userlevel'] >= "5") 
{
  $id = mres($_GET['id']);
  $title = generate_device_link($device);
  $auth = TRUE;
}

?>
