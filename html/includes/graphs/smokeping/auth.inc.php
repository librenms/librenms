<?php

if (is_numeric($vars['id']) && ($config['allow_unauth_graphs'] || device_permitted($vars['id'])))
{
  $device = device_by_id_cache($vars['id']);
  $title = generate_device_link($device);
  $graph_title = $device['hostname'];
  $auth = TRUE;
}

?>
