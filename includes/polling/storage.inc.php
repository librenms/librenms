<?php

$storage_cache = array();

#echo("Storage: ");

$query = "SELECT * FROM storage WHERE device_id = '" . $device['device_id'] . "'";
$storage_data = mysql_query($query);
while ($storage = mysql_fetch_assoc($storage_data))
{
  echo("Storage ".$storage['storage_descr'] . ": ");

  $storage_rrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("storage-" . $storage['storage_mib'] . "-" . safename($storage['storage_descr']) . ".rrd");

  if (!is_file($storage_rrd))
  {
   rrdtool_create($storage_rrd, "--step 300 \
     DS:used:GAUGE:600:0:U \
     DS:free:GAUGE:600:0:U \
     RRA:AVERAGE:0.5:1:600 \
     RRA:AVERAGE:0.5:6:700 \
     RRA:AVERAGE:0.5:24:775 \
     RRA:AVERAGE:0.5:288:797 \
     RRA:MIN:0.5:1:600 \
     RRA:MIN:0.5:6:700 \
     RRA:MIN:0.5:24:775 \
     RRA:MIN:0.5:288:797 \
     RRA:MAX:0.5:1:600 \
     RRA:MAX:0.5:6:700 \
     RRA:MAX:0.5:24:775 \
     RRA:MAX:0.5:288:797");
  }

  $file = $config['install_dir']."/includes/polling/storage-".$storage['storage_mib'].".inc.php";
  if (is_file($file))
  {
    include($file);
  } else {
    ### Generic poller goes here if we ever have a discovery module which uses it.
  }

  if ($debug) {print_r($storage); }

  if ($storage['size'])
  {
    $percent = round($storage['used'] / $storage['size'] * 100);
  }
  else
  {
    $percent = 0;
  }

  echo($percent."% ");

  rrdtool_update($storage_rrd,"N:".$storage['used'].":".$storage['free']);

  $update_query  = "UPDATE `storage` SET `storage_used` = '".$storage['used']."'";
  $update_query .= ", `storage_free` = '".$storage['free']."', `storage_size` = '".$storage['size']."'";
  $update_query .= ", `storage_units` = '".$storage['units']."', `storage_perc` = '".$percent."'";
  $update_query .= " WHERE `storage_id` = '".$storage['storage_id']."'";
  if ($debug) { echo("$update_query\n"); }
  mysql_query($update_query);

  echo("\n");
}

unset($storage);

?>