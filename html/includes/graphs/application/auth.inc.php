<?php

if(is_numeric($id) && application_permitted($id))
{
  $app    = get_application_by_id($id);
  $device = device_by_id_cache($app['device_id']);
  $title  = generatedevicelink($device);
  $title .= $graph_subtype;
  $auth   = TRUE;
}

?>
