<?php

if (is_numeric($vars['id']))
{
#  $auth= TRUE;
  $rserver = dbFetchRow("SELECT * FROM `loadbalancer_rservers` AS I, `devices` AS D WHERE I.rserver_id = ? AND I.device_id = D.device_id", array($vars['id']));

  if (is_numeric($rserver['device_id']) && ($config['allow_unauth_graphs'] || device_permitted($rserver['device_id'])))
  {
    $device = device_by_id_cache($rserver['device_id']);

    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("rserver-".$rserver['rserver_id'].".rrd");

    $title  = generate_device_link($device);
    $title .= " :: Rserver :: " . htmlentities($rserver['farm_id']);
    $auth = TRUE;
  }
}

?>
