<?php

if (is_numeric($id) && ($config['allow_unauth_graphs'] || port_permitted($id)))
{
  $port   = get_port_by_id($id);
  $device = device_by_id_cache($port['device_id']);
  $title  = generate_device_link($device);
  $title .= " :: Port  ".generate_port_link($port);

  $graph_title = shorthost($device['hostname']) . "::" . strtolower(makeshortif($port['ifDescr']));

  $auth   = TRUE;

  $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/port-" . safename($port['ifIndex'] . ".rrd");
}

?>
