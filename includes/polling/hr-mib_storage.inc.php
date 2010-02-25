<?php

/// HOST-RESOURCES-MIB - Storage Objects

$dq = mysql_query("SELECT * FROM storage WHERE device_id = '" . $device['device_id'] . "'");
while ($dr = mysql_fetch_array($dq)) {
  $storage_index = $dr['storage_index'];
  $storage_units = $dr['storage_units'];
  $storage_size = $dr['storage_units'] * $dr['storage_size']; 
  $storage_descr = $dr['storage_descr'];
  $cmd  = $config['snmpget'] . " -m HOST-RESOURCES-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " storage_used.$storage_index";
  $used_units = trim(shell_exec($cmd));
  $used = $used_units * $storage_units;
  $perc = round($used / $storage_size * 100, 2);
  $filedesc = str_replace("\"", "", str_replace("/", "_", $storage_descr));
  $old_storage_rrd = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("storage-" . $filedesc . ".rrd");
  $storage_rrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("hrStorage-" . $storage_index . ".rrd");
  if(is_file($old_storage_rrd)) { rename($old_storage_rrd,$storage_rrd); }

  if (!is_file($storage_rrd)) {
    shell_exec($config['rrdtool'] . " create $storage_rrd \
     --step 300 \
     DS:size:GAUGE:600:0:U \
     DS:used:GAUGE:600:0:U \
     DS:perc:GAUGE:600:0:U \
     RRA:AVERAGE:0.5:1:800 \
     RRA:AVERAGE:0.5:6:800 \
     RRA:AVERAGE:0.5:24:800 \
     RRA:AVERAGE:0.5:288:800 \
     RRA:MAX:0.5:1:800 \
     RRA:MAX:0.5:6:800 \
     RRA:MAX:0.5:24:800 \
     RRA:MAX:0.5:288:800");
  }  

  rrdtool_update($storage_rrd, "N:$storage_size:$used:$perc");
  mysql_query("UPDATE `storage` SET `storage_used` = '$used_units', `storage_perc` = '$perc' WHERE storage_id = '" . $dr['storage_id'] . "'");
  if (!is_numeric($dr['storage_perc_warn'])) { $dr['storage_perc_warn'] = 60; }
  if($dr['storage_perc'] < $dr['storage_perc_warn'] && $perc >= $dr['storage_perc_warn']) 
  {
    $msg = "Disk Alarm: " . $device['hostname'] . " " . $dr['storage_descr'] . " is " . $perc . "% at " . date($config['timestamp_format']);
    notify($device, "Disk Alarm: " . $device['hostname'] . " " . $dr['storage_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $dr['storage_descr'] . "\n");
  }
}
