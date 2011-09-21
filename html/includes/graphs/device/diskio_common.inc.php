<?php

$i = 1;

foreach (dbFetchRows("SELECT * FROM `ucd_diskio` AS U, `devices` AS D WHERE D.device_id = ? AND U.device_id = D.device_id", array($id)) as $disk)
{
  $rrd_filename = $config['rrd_dir'] . "/" . $disk['hostname'] . "/ucd_diskio-" . safename($disk['diskio_descr'] . ".rrd");
  if (is_file($rrd_filename))
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $disk['diskio_descr'];
    $rrd_list[$i]['ds_in'] = $ds_in;
    $rrd_list[$i]['ds_out'] = $ds_out;
    $i++;
  }
}


?>
