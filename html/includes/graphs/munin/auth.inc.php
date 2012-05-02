<?php

if (is_numeric($id))
{
  $mplug = dbFetchRow("SELECT * FROM `munin_plugins` WHERE `mplug_id` = ?", array($id));

  if (is_numeric($mplug['device_id']) && ($config['allow_unauth_graphs'] || device_permitted($mplug['device_id'])))
  {
    $device = device_by_id_cache($mplug['device_id']);
    $title  = generate_device_link($device);
#    if (!empty($mplug['mplug_instance']))
#    {
#      $plugfile = $config['rrd_dir']."/".$device['hostname']."/munin/".$mplug['mplug_type']."_".$mplug['mplug_instance'];
#      $title .= " :: Plugin :: " . $mplug['mplug_type'] . " :: " . $mplug['mplug_title'];
#    } else {
      $plugfile = $config['rrd_dir']."/".$device['hostname']."/munin/".$mplug['mplug_type'];
      $title .= " :: Plugin :: " . $mplug['mplug_type']  . " - " . $mplug['mplug_title'];
#    }
    $auth = TRUE;
  }
}

?>
