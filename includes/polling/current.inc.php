<?php

$query = "SELECT * FROM sensors WHERE sensor_class='current' AND device_id = '" . $device['device_id'] . "' AND poller_type='snmp'";
$current_data = mysql_query($query);
while ($dbcurrent = mysql_fetch_assoc($current_data))
{
  echo("Checking current " . $dbcurrent['sensor_descr'] . "... ");

  $current = snmp_get($device, $dbcurrent['sensor_oid'], "-OUqnv", "SNMPv2-MIB");

  if ($dbcurrent['sensor_divisor']) { $current = $current / $dbcurrent['sensor_divisor']; }
  if ($dbcurrent['sensor_multplier']) { $current = $current * $dbcurrent['sensor_multiplier']; }

  $currentrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("current-" . $dbcurrent['sensor_descr'] . ".rrd");

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
  if ($dbcurrent['sensor_limit_low'] != "" && $dbcurrent['sensor_current'] > $dbcurrent['sensor_limit_low'] && $current <= $dbcurrent['sensor_limit_low'])
  {
    $msg  = "Current Alarm: " . $device['hostname'] . " " . $dbcurrent['sensor_descr'] . " is under threshold: " . $current . "A (< " . $dbcurrent['sensor_limit'];
    $msg .= "A) at " . date($config['timestamp_format']);
    notify($device, "Current Alarm: " . $device['hostname'] . " " . $dbcurrent['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $dbcurrent['sensor_descr'] . "\n");
    log_event('Current ' . $dbcurrent['sensor_descr'] . " under threshold: " . $current . " A (< " . $dbcurrent['sensor_limit_low'] . " A)", $device, 'current', $current['sensor_id']);
  }
  else if ($dbcurrent['sensor_limit_low'] != "" && $dbcurrent['sensor_current'] < $dbcurrent['sensor_limit'] && $current >= $dbcurrent['sensor_limit'])
  {
    $msg  = "Current Alarm: " . $device['hostname'] . " " . $dbcurrent['sensor_descr'] . " is over threshold: " . $current . "A (> " . $dbcurrent['sensor_limit'];
    $msg .= "A) at " . date($config['timestamp_format']);
    notify($device, "Current Alarm: " . $device['hostname'] . " " . $dbcurrent['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $dbcurrent['sensor_descr'] . "\n");
    log_event('Current ' . $dbcurrent['sensor_descr'] . " above threshold: " . $current . " A (> " . $dbcurrent['sensor_limit'] . " A)", $device, 'current', $current['sensor_id']);
  }

  mysql_query("UPDATE sensors SET sensor_current = '$current' WHERE sensor_class='current' AND sensor_id = '" . $dbcurrent['sensor_id'] . "'");
}

?>