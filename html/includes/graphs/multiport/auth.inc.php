<?php

$auth = TRUE;

foreach (explode(",", $vars['id']) as $ifid)
{
  if (!$auth && !port_permitted($ifid))
  $auth = FALSE;
}

#if (is_numeric($vars['id'])) {
#  $port = get_port_by_id($vars['id']);
#  $device = device_by_id_cache($port['device_id']);
#}

#$title  = generate_device_link($device);
#$title .= " :: Port  ".generate_port_link($port);

$title = "Multi Port :: ";

?>
