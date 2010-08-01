<?php

if(is_numeric($id))
{

  $query = mysql_query("SELECT * FROM `ucd_diskio` AS U, `devices` AS D WHERE U.diskio_id = '".$id."' AND U.device_id = D.device_id");
  $disk = mysql_fetch_array($query);

  if(is_numeric($disk['device_id']) && device_permitted($disk['device_id']))
  {
    $device = device_by_id_cache($disk['device_id']);

    $rrd_filename = $config['rrd_dir'] . "/" . $disk['hostname'] . "/ucd_diskio-" . safename($disk['diskio_descr'] . ".rrd");

    $title  = generatedevicelink($device);
    $title .= " :: Disk :: " . htmlentities($disk['diskio_descr']);
    $auth = TRUE;
  }
}




?>
