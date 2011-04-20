<?php

function poll_sensor($device, $class, $unit)
{
  global $config;

  $query = "SELECT * FROM sensors WHERE sensor_class='$class' AND device_id = '" . $device['device_id'] . "' AND poller_type='snmp'";  
  $sensor_data = mysql_query($query);

  while ($sensor = mysql_fetch_assoc($sensor_data))
  {
    echo("Checking $class " . $sensor['sensor_descr'] . "... ");

    $sensor_value = snmp_get($device, $sensor['sensor_oid'], "-OUqnv", "SNMPv2-MIB");

    if ($sensor['sensor_divisor'])    { $sensor_value = $sensor_value / $sensor['sensor_divisor']; }
    if ($sensor['sensor_multiplier']) { $sensor_value = $sensor_value * $sensor['sensor_multiplier']; }

    $rrd_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("$class-" . $sensor['sensor_descr'] . ".rrd");

    if (!is_file($rrd_file))
    {

      rrdtool_create($rrd_file,"--step 300 \
      DS:sensor:GAUGE:600:-20000:20000 \
      RRA:AVERAGE:0.5:1:1200 \
      RRA:AVERAGE:0.5:12:2400 \
      RRA:AVERAGE:0.5:288:750 \
      RRA:MAX:0.5:12:2400 \
      RRA:MAX:0.5:288:750 \
      RRA:MIN:0.5:12:2400 \
      RRA:MIN:0.5:288:750");
    }

    echo("$sensor_value $unit\n");

    rrdtool_update($rrd_file,"N:$sensor_value");

    # FIXME also warn when crossing WARN level!!
    if ($sensor['sensor_limit_low'] != "" && $sensor['sensor_current'] > $sensor['sensor_limit_low'] && $sensor_value <= $sensor['sensor_limit_low'])
    {
      $msg  = ucfirst($class) . " Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is under threshold: " . $sensor_value . "$unit (< " . $sensor['sensor_limit'];
      $msg .= "$unit) at " . date($config['timestamp_format']);
      notify($device, ucfirst($class) . " Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
      echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
      log_event(ucfirst($class) . ' ' . $sensor['sensor_descr'] . " under threshold: " . $sensor_value . " $unit (< " . $sensor['sensor_limit_low'] . " $unit)", $device, $class, $sensor['sensor_id']);
    }
    else if ($sensor['sensor_limit'] != "" && $sensor['sensor_current'] < $sensor['sensor_limit'] && $sensor_value >= $sensor['sensor_limit'])
    {
      $msg  = ucfirst($class) . " Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is over threshold: " . $sensor_value . "$unit (> " . $sensor['sensor_limit'];
      $msg .= "$unit) at " . date($config['timestamp_format']);
      notify($device, ucfirst($class) . " Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
      echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
      log_event(ucfirst($class) . ' ' . $sensor['sensor_descr'] . " above threshold: " . $sensor_value . " $unit (> " . $sensor['sensor_limit'] . " $unit)", $device, $class, $sensor['sensor_id']);
    }

    mysql_query("UPDATE sensors SET sensor_current = '$sensor_value' WHERE sensor_class='$class' AND sensor_id = '" . $sensor['sensor_id'] . "'");
  }
}

?>
