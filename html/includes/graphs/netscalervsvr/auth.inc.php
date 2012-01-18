<?php

if (is_numeric($id))
{

  $vsvr = dbFetchRow("SELECT * FROM `netscaler_vservers` AS I, `devices` AS D WHERE I.vsvr_id = ? AND I.device_id = D.device_id", array($id));

  if (is_numeric($vsvr['device_id']) && ($config['allow_unauth_graphs'] || device_permitted($vsvr['device_id'])))
  {
    $device = device_by_id_cache($vsvr['device_id']);

    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("netscaler-vsvr-".$vsvr['vsvr_name'].".rrd");

    $title  = generate_device_link($device);
    $title .= " :: Netscaler VServer :: " . htmlentities($vsvr['vsvr_name']);
    $auth = TRUE;
  }
}

?>
