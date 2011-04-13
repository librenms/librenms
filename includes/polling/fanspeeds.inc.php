<?php

$query = "SELECT * FROM sensors WHERE sensor_class='fanspeed' AND device_id = '" . $device['device_id'] . "' AND poller_type='snmp'";
$fan_data = mysql_query($query);

while ($sensor = mysql_fetch_assoc($fan_data))
{
  echo("Checking fan " . $sensor['sensor_descr'] . "... ");

  $fan = snmp_get($device, $sensor['sensor_oid'], "-OUqnv", "SNMPv2-MIB");

  if ($sensor['sensor_divisor'])    { $fan = $fan / $sensor['sensor_divisor']; }
  if ($sensor['sensor_multiplier']) { $fan = $fan * $sensor['sensor_multiplier']; }

  $old_rrd_file  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("fanspeed-" . $sensor['sensor_descr'] . ".rrd");
  $rrd_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/fanspeed-" . safename($sensor['sensor_type']."-".$sensor['sensor_index']) . ".rrd";

  if (is_file($old_rrd_file)) { rename($old_rrd_file, $rrd_file); }

  if (!is_file($rrd_file))
  {
     rrdtool_create($rrd_file,"--step 300 \
     DS:sensor:GAUGE:600:0:20000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400");
  }

  echo($fan . " rpm\n");

  rrdtool_update($rrd_file,"N:$fan");

  if ($sensor['sensor_limit_low'] != "" && $sensor['sensor_current'] > $sensor['sensor_limit_low'] && $fan <= $sensor['sensor_limit_low'])
  {
    $msg  = "Fan Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is " . $fan . "rpm (Limit " . $sensor['sensor_limit_low'];
    $msg .= "rpm) at " . date($config['timestamp_format']);
    notify($device, "Fan Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
    log_event('Fan speed ' . $sensor['sensor_descr'] . " under threshold: " . $sensor['sensor_current'] . " rpm (<= " . $sensor['sensor_limit_low'] . " rpm)", $device, 'fanspeed', $sensor['sensor_id']);
  }
  else if ($sensor['sensor_limit_low_warn'] != "" && $sensor['sensor_limit_low_warn'] && $sensor['sensor_current'] > $sensor['sensor_limit_warn'] && $fan <= $sensor['sensor_limit_low_warn'])
  {
    $msg  = "Fan Warning: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is " . $fan . "rpm (Warning limit " . $sensor['sensor_limit_low_warn'];
    $msg .= "rpm) at " . date($config['timestamp_format']);
    notify($device, "Fan Warning: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
    log_event('Fan speed ' . $sensor['sensor_descr'] . " under warning threshold: " . $sensor['sensor_current'] . " rpm (<= " . $sensor['sensor_limit_low_warn'] . " rpm)", $device, 'fanspeed', $sensor['sensor_id']);
  }

  mysql_query("UPDATE sensors SET sensor_current = '$fan' WHERE sensor_class='fanspeed' AND sensor_id = '" . $sensor['sensor_id'] . "'");
}

?>