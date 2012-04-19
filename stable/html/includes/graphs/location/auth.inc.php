<?php

foreach (dbFetchRows("SELECT * FROM `devices` WHERE `location` = ?", array($id)) as $device)
{
  if ($config['allow_unauth_graphs'] || device_permitted($device_id))
  {
    $devices[] = $device;
    $title = $id;
    $auth = TRUE;
  }
}

?>
