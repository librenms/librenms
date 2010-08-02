<?php

if(is_numeric($id) && device_permitted($id)) 
{
  $device = device_by_id_cache($id);
  $title = generate_device_link($device);
  $auth = TRUE;
}

?>
