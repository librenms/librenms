<?php

$query = "SELECT * FROM `sensors` WHERE device_id = '" . $device['device_id'] . "' AND `sensor_class` = 'freq'";
$sensor_data = mysql_query($query);
while($frequency = mysql_fetch_array($sensor_data)) {

  echo("Checking frequency " . $frequency['sensor_descr'] . "... ");

  $freq = snmp_get($device, $frequency['sensor_oid'], "-OUqnv", "SNMPv2-MIB");

  if ($frequency['sensor_precision']) 
  {
    $freq = $freq / $frequency['sensor_precision'];
  }

  $rrd_file      = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("freq-" . $frequency['sensor_descr'] . ".rrd");

  if (!is_file($rrd_file)) {
    `rrdtool create $rrd_file \
     --step 300 \
     DS:freq:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400`;
  }

  echo($freq . " Hz\n");

  rrdtool_update($rrd_file,"N:$freq");

  if($frequency['sensor_current'] > $frequency['sensor_limit_low'] && $freq <= $frequency['sensor_limit_low']) 
  {
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Frequency Alarm: " . $device['hostname'] . " " . $frequency['sensor_descr'] . " is " . $freq . "Hz (Limit " . $frequency['sensor_limit'];
    $msg .= "Hz) at " . date($config['timestamp_format']);
    notify($device, "Frequency Alarm: " . $device['hostname'] . " " . $frequency['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $frequency['sensor_descr'] . "\n");
    log_event('Frequency ' . $frequency['sensor_descr'] . " under threshold: " . $freq . " Hz (< " . $frequency['sensor_limit_low'] . " Hz)", $device['device_id'] , 'frequency', $frequency['sensor_id']);
  }
  else if($frequency['sensor_current'] < $frequency['sensor_limit'] && $freq >= $frequency['sensor_limit']) 
  {
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Frequency Alarm: " . $device['hostname'] . " " . $frequency['sensor_descr'] . " is " . $freq . "Hz (Limit " . $frequency['sensor_limit'];
    $msg .= "Hz) at " . date($config['timestamp_format']);
    notify($device, "Frequency Alarm: " . $device['hostname'] . " " . $frequency['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $frequency['sensor_descr'] . "\n");
    log_event('Frequency ' . $frequency['sensor_descr'] . " above threshold: " . $freq . " Hz (> " . $frequency['sensor_limit'] . " Hz)", $device['device_id'], 'frequency', $frequency['sensor_id']);
  }

  mysql_query("UPDATE frequency SET sensor_current = '$freq' WHERE sensor_id = '" . $frequency['sensor_id'] . "'");
}

?>
