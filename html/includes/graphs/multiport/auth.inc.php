<?php

$auth = TRUE;

foreach (explode(",", $id) as $ifid)
{
  if (!$config['allow_unauth_graphs'] && !port_permitted($ifid))
  $auth = FALSE;
}

#if (is_numeric($id)) {
#  $port = get_port_by_id($id);
#  $device = device_by_id_cache($port['device_id']);
#}

#$title  = generate_device_link($device);
#$title .= " :: Port  ".generate_port_link($port);

$title = "Multi Port :: ";

?>