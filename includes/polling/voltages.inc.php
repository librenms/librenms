<?php

$query = "SELECT * FROM sensors WHERE sensor_class='voltage' AND device_id = '" . $device['device_id'] . "' AND poller_type='snmp'";
$volt_data = mysql_query($query);

while ($sensor = mysql_fetch_array($volt_data))
{
  echo("Checking voltage " . $sensor['sensor_descr'] . "... ");

  $volt = snmp_get($device, $sensor['sensor_oid'], "-OUqnv", "SNMPv2-MIB");

  if ($sensor['sensor_divisor'])    { $volt = $volt / $sensor['sensor_divisor']; }
  if ($sensor['sensor_multiplier']) { $volt = $volt * $sensor['sensor_multiplier']; }

  $old_rrd_file  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("voltage-" . $sensor['sensor_descr'] . ".rrd");
  $rrd_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/voltage-" . safename($sensor['sensor_type']."-".$sensor['sensor_index']) . ".rrd";

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

  echo($volt . " V\n");

  rrdtool_update($rrd_file,"N:$volt");

  if ($sensor['sensor_current'] > $sensor['sensor_limit_low'] && $volt <= $sensor['sensor_limit_low'])
  {
    $msg  = "Voltage Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is " . $volt . "V (Limit " . $sensor['sensor_limit'];
    $msg .= "V) at " . date($config['timestamp_format']);
    notify($device, "Voltage Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
    log_event('Voltage ' . $sensor['sensor_descr'] . " under threshold: " . $volt . " V (< " . $sensor['sensor_limit_low'] . " V)", $device['device_id'], 'voltage', $sensor['sensor_id']);
  }
  else if ($sensor['sensor_current'] < $sensor['sensor_limit'] && $volt >= $sensor['sensor_limit'])
  {
    $msg  = "Voltage Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is " . $volt . "V (Limit " . $sensor['sensor_limit'];
    $msg .= "V) at " . date($config['timestamp_format']);
    notify($device, "Voltage Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
    log_event('Voltage ' . $sensor['sensor_descr'] . " above threshold: " . $volt . " V (> " . $sensor['sensor_limit'] . " V)", $device['device_id'], 'voltage', $sensor['sensor_id']);
  }
  mysql_query("UPDATE sensors SET sensor_current = '$volt' WHERE sensor_class='voltage' AND sensor_id = '" . $sensor['sensor_id'] . "'");
}

?>