<?php

if (is_numeric($id))
{
  $storage = dbFetchRow("SELECT * FROM `storage` WHERE `storage_id` = ?", array($id));

  if (is_numeric($storage['device_id']) && ($config['allow_unauth_graphs'] || device_permitted($storage['device_id'])))
  {
    $device = device_by_id_cache($storage['device_id']);
    $rrd_filename  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("storageX-" . $storage['storage_mib'] . "-" . $storage['storage_descr'] . ".rrd");

    $title  = generate_device_link($device);
    $title .= " :: Storage :: " . htmlentities($storage['storage_descr']);
    $auth = TRUE;
  }
}

?>
