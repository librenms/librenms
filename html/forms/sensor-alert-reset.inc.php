<?php

/*
  Copyright (C) 2013 LibreNMS Contributors librenms-project@googlegroups.com
*/

// FUA

for($x=0;$x<count($_POST['sensor_id']);$x++)
{
  dbUpdate(array('sensor_limit' => $_POST['sensor_limit'][$x], 'sensor_limit_low' => $_POST['sensor_limit_low'][$x], 'sensor_alert' => $_POST['sensor_alert'][$x]), 'sensors', '`sensor_id` = ?', array($_POST['sensor_id'][$x]));
}

