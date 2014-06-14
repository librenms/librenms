<?php

/*
  Copyright (C) 2013 LibreNMS Contributors librenms-project@googlegroups.com
*/

// FUA

if(!is_numeric($_POST['device_id']) || !is_numeric($_POST['sensor_id']) || (empty($_POST['data']) || !isset($_POST['data'])))
{
  echo('error with data');
  exit;
}
else
{
  $update = dbUpdate(array($_POST['value_type'] => $_POST['data']), 'sensors', '`sensor_id` = ? AND `device_id` = ?', array($_POST['sensor_id'],$_POST['device_id']));
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

