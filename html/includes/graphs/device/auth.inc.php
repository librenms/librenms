<?php

if(is_numeric($id) && device_permitted($id)) 
{
  $device = device_by_id_cache($id);
  $title = generatedevicelink($device);
  $auth = TRUE;
}

?>
