<?php

/*
  Copyright (C) 2013 LibreNMS Contributors librenms-project@googlegroups.com
*/

// FUA

if(!is_numeric($_POST['device_id']) || !is_numeric($_POST['sensor_id']))
{
  echo('error with data');
  exit;
}
else
{
  if($_POST['state'] == 'true')
  {
    $state = 1;
  }
  elseif($_POST['state'] == 'false')
  {
    $state = 0;
  }
  $update = dbUpdate(array('sensor_alert' => $state), 'sensors', '`sensor_id` = ? AND `device_id` = ?', array($_POST['sensor_id'],$_POST['device_id']));
  if(!empty($update) || $update == '0')
  {
    echo('success');
    exit;
  }
  else
  {
    echo('error');
    exit;
  }
}

