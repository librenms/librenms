<?php

if (is_numeric($vars['id']))
{
  $disk = dbFetchRow("SELECT * FROM `ucd_diskio` AS U, `devices` AS D WHERE U.diskio_id = ? AND U.device_id = D.device_id", array($vars['id']));

  if (is_numeric($disk['device_id']) && ($config['allow_unauth_graphs'] || device_permitted($disk['device_id'])))
  {
    $device = device_by_id_cache($disk['device_id']);

    $rrd_filename = $config['rrd_dir'] . "/" . $disk['hostname'] . "/ucd_diskio-" . safename($disk['diskio_descr'] . ".rrd");

    $title  = generate_device_link($device);
    $title .= " :: Disk :: " . htmlentities($disk['diskio_descr']);
    $auth = TRUE;
  }
}

?>
