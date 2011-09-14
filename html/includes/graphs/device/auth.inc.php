<?php

if (is_numeric($id) && ($config['allow_unauth_graphs'] || device_permitted($id)))
{
  $device = device_by_id_cache($id);
  $title = generate_device_link($device);
  $graph_title = $device['hostname'];
  $auth = TRUE;
}

?>
