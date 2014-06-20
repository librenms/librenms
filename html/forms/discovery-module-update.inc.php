<?php

// FUA

$device['device_id'] = $_POST['device_id'];
$module = 'discover_'.$_POST['discovery_module'];

if(!isset($module) && !isset($device_id) && !is_numeric($device_id))
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
  else
  {
    $state = 0;
  }

  if(isset($attribs['discover_'.$module]) && $attribs['discover_'.$module] != $config['discover_modules'][$module])
  {
    del_dev_attrib($device, $module);
  }
  else
  {
    set_dev_attrib($device, $module, $state);
  }
}

