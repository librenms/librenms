<?php

$query = "SELECT * FROM sensors WHERE sensor_class='humidity' AND device_id = '" . $device['device_id'] . "' AND poller_type='snmp'";
$hum_data = mysql_query($query);
while ($sensor = mysql_fetch_assoc($hum_data))
{
  echo("Checking humidity " . $sensor['sensor_descr'] . "... ");

  $hum = snmp_get($device, $sensor['sensor_oid'], "-OUqnv", "SNMPv2-MIB");

  if ($sensor['sensor_divisor'])    { $hum = $hum / $sensor['sensor_divisor']; }
  if ($sensor['sensor_multiplier']) { $hum = $hum / $sensor['sensor_multiplier']; }

  $humrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("humidity-" . $sensor['sensor_descr'] . ".rrd");

  if (!is_file($humrrd))
  {
    rrdtool_create($humrrd,"--step 300 \
     DS:sensor:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400");
  }

  echo($hum . " %\n");

  rrdtool_update($humrrd,"N:$hum");

  if ($sensor['sensor_limit_low'] != "" && $sensor['sensor_current'] > $sensor['sensor_limit_low'] && $hum <= $sensor['sensor_limit_low'])
  {
    $msg  = "Humidity Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is " . $hum . "% (Limit " . $sensor['sensor_limit'];
    $msg .= "%) at " . date($config['timestamp_format']);
    notify($device, "Humidity Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
    log_event('Frequency ' . $sensor['sensor_descr'] . " under threshold: " . $hum . " % (< " . $sensor['sensor_limit_low'] . " %)", $device, 'humidity', $sensor['sensor_id']);
  }
  else if ($sensor['sensor_limit'] != "" && $sensor['sensor_current'] < $sensor['sensor_limit'] && $hum >= $sensor['sensor_limit'])
  {
    $msg  = "Humidity Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is " . $hum . "% (Limit " . $sensor['sensor_limit'];
    $msg .= "%) at " . date($config['timestamp_format']);
    notify($device, "Humidity Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
    log_event('Humidity ' . $sensor['sensor_descr'] . " above threshold: " . $hum . " % (> " . $sensor['sensor_limit'] . " %)", $device, 'humidity', $sensor['sensor_id']);
  }

  mysql_query("UPDATE sensors SET sensor_current = '$hum' WHERE sensor_class='humidity' AND sensor_id = '" . $sensor['sensor_id'] . "'");
}

?>