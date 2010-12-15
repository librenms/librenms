<?php

$query = "SELECT * FROM sensors WHERE sensor_class='humidity' AND device_id = '" . $device['device_id'] . "' AND poller_type='snmp'";
$hum_data = mysql_query($query);
while($humidity = mysql_fetch_array($hum_data)) {

  echo("Checking humidity " . $humidity['sensor_descr'] . "... ");

  $hum = snmp_get($device, $humidity['sensor_oid'], "-OUqnv", "SNMPv2-MIB");

  if ($humidity['sensor_divisor'])    { $hum = $hum / $humidity['sensor_divisor']; }
  if ($humidity['sensor_multiplier']) { $hum = $hum / $humidity['sensor_multiplier']; }

  $humrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("humidity-" . $humidity['sensor_descr'] . ".rrd");

  if (!is_file($humrrd)) {
    `rrdtool create $humrrd \
     --step 300 \
     DS:sensor:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400`;
  }

  echo($hum . " %\n");

  rrdtool_update($humrrd,"N:$hum");

  if($humidity['sensor_current'] > $humidity['sensor_limit_low'] && $hum <= $humidity['sensor_limit_low']) 
  {
    $msg  = "Humidity Alarm: " . $device['hostname'] . " " . $humidity['sensor_descr'] . " is " . $hum . "% (Limit " . $humidity['sensor_limit'];
    $msg .= "%) at " . date($config['timestamp_format']);
    notify($device, "Humidity Alarm: " . $device['hostname'] . " " . $humidity['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $humidity['sensor_descr'] . "\n");
    log_event('Frequency ' . $humidity['sensor_descr'] . " under threshold: " . $hum . " % (< " . $humidity['sensor_limit_low'] . " %)", $device['device_id'] , 'humidity', $humidity['sensor_id']);
  }
  else if($humidity['sensor_current'] < $humidity['sensor_limit'] && $hum >= $humidity['sensor_limit']) 
  {
    $msg  = "Humidity Alarm: " . $device['hostname'] . " " . $humidity['sensor_descr'] . " is " . $hum . "% (Limit " . $humidity['sensor_limit'];
    $msg .= "%) at " . date($config['timestamp_format']);
    notify($device, "Humidity Alarm: " . $device['hostname'] . " " . $humidity['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $humidity['sensor_descr'] . "\n");
    log_event('Humidity ' . $humidity['sensor_descr'] . " above threshold: " . $hum . " % (> " . $humidity['sensor_limit'] . " %)", $device['device_id'], 'humidity', $humidity['sensor_id']);
  }

  mysql_query("UPDATE sensors SET sensor_current = '$hum' WHERE sensor_class='humidity' AND sensor_id = '" . $humidity['sensor_id'] . "'");
}

?>
