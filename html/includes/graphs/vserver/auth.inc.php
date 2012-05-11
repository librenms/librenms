<?php

if (is_numeric($vars['id']))
{
#  $auth= TRUE;
  $vserver = dbFetchRow("SELECT * FROM `loadbalancer_vservers` AS I, `devices` AS D WHERE I.classmap_id = ? AND I.device_id = D.device_id", array($vars['id']));

  if (is_numeric($vserver['device_id']) && ($config['allow_unauth_graphs'] || device_permitted($vserver['device_id'])))
  {
    $device = device_by_id_cache($vserver['device_id']);

    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("vserver-".$vserver['classmap_id'].".rrd");

    $title  = generate_device_link($device);
    $title .= " :: Serverfarm :: " . htmlentities($vserver['classmap_id']);
    $auth = TRUE;
  }
}

?>
