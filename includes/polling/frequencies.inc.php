<?php

$query = "SELECT * FROM `sensors` WHERE device_id = '" . $device['device_id'] . "' AND `sensor_class` = 'freq' AND poller_type='snmp'";
$sensor_data = mysql_query($query);

while ($sensor = mysql_fetch_array($sensor_data))
{
  echo("Checking frequency " . $sensor['sensor_descr'] . "... ");

  $freq = snmp_get($device, $sensor['sensor_oid'], "-OUqnv", "SNMPv2-MIB");

  if ($sensor['sensor_divisor'])    { $freq = $freq / $sensor['sensor_divisor']; }
  if ($sensor['sensor_multiplier']) { $freq = $freq * $sensor['sensor_multiplier']; }

  $rrd_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("frequency-" . $sensor['sensor_descr'] . ".rrd");

  if (!is_file($rrd_file))
  {
    rrdtool_create($rrd_file,"--step 300 \
     DS:sensor:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400");
  }

  echo($freq . " Hz\n");

  rrdtool_update($rrd_file,"N:$freq");

  if ($sensor['sensor_current'] > $sensor['sensor_limit_low'] && $freq <= $sensor['sensor_limit_low'])
  {
    $msg  = "Frequency Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is " . $freq . "Hz (Limit " . $sensor['sensor_limit'];
    $msg .= "Hz) at " . date($config['timestamp_format']);
    notify($device, "Frequency Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
    log_event('Frequency ' . $sensor['sensor_descr'] . " under threshold: " . $freq . " Hz (< " . $sensor['sensor_limit_low'] . " Hz)", $device, 'frequency', $sensor['sensor_id']);
  }
  else if ($sensor['sensor_current'] < $sensor['sensor_limit'] && $freq >= $sensor['sensor_limit'])
  {
    $msg  = "Frequency Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is " . $freq . "Hz (Limit " . $sensor['sensor_limit'];
    $msg .= "Hz) at " . date($config['timestamp_format']);
    notify($device, "Frequency Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
    log_event('Frequency ' . $sensor['sensor_descr'] . " above threshold: " . $freq . " Hz (> " . $sensor['sensor_limit'] . " Hz)", $device, 'frequency', $sensor['sensor_id']);
  }

  mysql_query("UPDATE frequency SET sensor_current = '$freq' WHERE sensor_id = '" . $sensor['sensor_id'] . "'");
}

?>