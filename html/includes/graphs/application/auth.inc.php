<?php

if (is_numeric($id) && ($config['allow_unauth_graphs'] || application_permitted($id)))
{
  $app    = get_application_by_id($id);
  $device = device_by_id_cache($app['device_id']);
  $title  = generate_device_link($device);
  $title .= $graph_subtype;
  $auth   = TRUE;
}

?>