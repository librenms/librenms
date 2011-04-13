<?php

$class = 'voltage';
$unit = 'V';

$query = "SELECT * FROM sensors WHERE sensor_class='$class' AND device_id = '" . $device['device_id'] . "' AND poller_type='snmp'";  
$sensor_data = mysql_query($query);

while ($sensor = mysql_fetch_assoc($sensor_data))
{
  echo("Checking voltage " . $sensor['sensor_descr'] . "... ");

  $sensor_value = snmp_get($device, $sensor['sensor_oid'], "-OUqnv", "SNMPv2-MIB");

  if ($sensor['sensor_divisor'])    { $sensor_value = $sensor_value / $sensor['sensor_divisor']; }
  if ($sensor['sensor_multiplier']) { $sensor_value = $sensor_value * $sensor['sensor_multiplier']; }

  $old_rrd_file  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("$class-" . $sensor['sensor_descr'] . ".rrd");
  $rrd_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/$class-" . safename($sensor['sensor_type']."-".$sensor['sensor_index']) . ".rrd";

  if (is_file($old_rrd_file)) { rename($old_rrd_file, $rrd_file); }

  if (!is_file($rrd_file))
  {
    rrdtool_create($rrd_file,"--step 300 \
     DS:sensor:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400");
  }

  echo("$sensor_value $unit\n");

  rrdtool_update($rrd_file,"N:$sensor_value");

  if ($sensor['sensor_limit_low'] != "" && $sensor['sensor_current'] > $sensor['sensor_limit_low'] && $sensor_value <= $sensor['sensor_limit_low'])
  {
    $msg  = "Voltage Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is " . $sensor_value . "$unit (Limit " . $sensor['sensor_limit'];
    $msg .= "$unit) at " . date($config['timestamp_format']);
    notify($device, "Voltage Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
    log_event('Voltage ' . $sensor['sensor_descr'] . " under threshold: " . $sensor_value . " $unit (< " . $sensor['sensor_limit_low'] . " $unit)", $device, $class, $sensor['sensor_id']);
  }
  else if ($sensor['sensor_limit'] != "" && $sensor['sensor_current'] < $sensor['sensor_limit'] && $sensor_value >= $sensor['sensor_limit'])
  {
    $msg  = "Voltage Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is " . $sensor_value . "$unit (Limit " . $sensor['sensor_limit'];
    $msg .= "$unit) at " . date($config['timestamp_format']);
    notify($device, "Voltage Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
    log_event('Voltage ' . $sensor['sensor_descr'] . " above threshold: " . $sensor_value . " $unit (> " . $sensor['sensor_limit'] . " $unit)", $device, $class, $sensor['sensor_id']);
  }
  mysql_query("UPDATE sensors SET sensor_current = '$sensor_value' WHERE sensor_class='$class' AND sensor_id = '" . $sensor['sensor_id'] . "'");
}

?>