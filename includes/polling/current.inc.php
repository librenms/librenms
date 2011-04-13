<?php

$query = "SELECT * FROM sensors WHERE sensor_class='current' AND device_id = '" . $device['device_id'] . "' AND poller_type='snmp'";
$current_data = mysql_query($query);
while ($sensor = mysql_fetch_assoc($current_data))
{
  echo("Checking current " . $sensor['sensor_descr'] . "... ");

  $current = snmp_get($device, $sensor['sensor_oid'], "-OUqnv", "SNMPv2-MIB");

  if ($sensor['sensor_divisor']) { $current = $current / $sensor['sensor_divisor']; }
  if ($sensor['sensor_multplier']) { $current = $current * $sensor['sensor_multiplier']; }

  $currentrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("current-" . $sensor['sensor_descr'] . ".rrd");

  if (!is_file($currentrrd))
  {
    rrdtool_create($currentrrd,"--step 300 \
     DS:sensor:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400");
  }

  echo($current . " A\n");

  rrdtool_update($currentrrd,"N:$current");

# FIXME also warn when crossing WARN level!!
  if ($sensor['sensor_limit_low'] != "" && $sensor['sensor_current'] > $sensor['sensor_limit_low'] && $current <= $sensor['sensor_limit_low'])
  {
    $msg  = "Current Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is under threshold: " . $current . "A (< " . $sensor['sensor_limit'];
    $msg .= "A) at " . date($config['timestamp_format']);
    notify($device, "Current Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
    log_event('Current ' . $sensor['sensor_descr'] . " under threshold: " . $current . " A (< " . $sensor['sensor_limit_low'] . " A)", $device, 'current', $current['sensor_id']);
  }
  else if ($sensor['sensor_limit'] != "" && $sensor['sensor_current'] < $sensor['sensor_limit'] && $current >= $sensor['sensor_limit'])
  {
    $msg  = "Current Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is over threshold: " . $current . "A (> " . $sensor['sensor_limit'];
    $msg .= "A) at " . date($config['timestamp_format']);
    notify($device, "Current Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
    log_event('Current ' . $sensor['sensor_descr'] . " above threshold: " . $current . " A (> " . $sensor['sensor_limit'] . " A)", $device, 'current', $current['sensor_id']);
  }

  mysql_query("UPDATE sensors SET sensor_current = '$current' WHERE sensor_class='current' AND sensor_id = '" . $sensor['sensor_id'] . "'");
}

?>