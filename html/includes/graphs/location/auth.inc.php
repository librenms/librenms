<?php

foreach (dbFetchRows("SELECT * FROM `devices` WHERE `location` = ?", array($vars['id'])) as $device)
{
  if ($auth || device_permitted($device_id))
  {
    $devices[] = $device;
    $title = $vars['id'];
    $auth = TRUE;
  }
}

?>
