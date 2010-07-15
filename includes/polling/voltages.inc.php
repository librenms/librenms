<?php

$query = "SELECT * FROM sensors WHERE sensor_class='voltage' AND device_id = '" . $device['device_id'] . "'";
$volt_data = mysql_query($query);
while($voltage = mysql_fetch_array($volt_data)) {

  echo("Checking voltage " . $voltage['sensor_descr'] . "... ");

  $volt = snmp_get($device, $voltage['sensor_oid'], "-OUqnv", "SNMPv2-MIB");

  if ($voltage['sensor_precision']) 
  {
    $volt = $volt / $voltage['sensor_precision'];
  }

  $voltrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("volt-" . $voltage['sensor_descr'] . ".rrd");

  if (!is_file($voltrrd)) {
    `rrdtool create $voltrrd \
     --step 300 \
     DS:volt:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400`;
  }

  echo($volt . " V\n");

  rrdtool_update($voltrrd,"N:$volt");

  if($voltage['sensor_current'] > $voltage['sensor_limit_low'] && $volt <= $voltage['sensor_limit_low']) 
  {
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Voltage Alarm: " . $device['hostname'] . " " . $voltage['sensor_descr'] . " is " . $volt . "V (Limit " . $voltage['sensor_limit'];
    $msg .= "V) at " . date($config['timestamp_format']);
    notify($device, "Voltage Alarm: " . $device['hostname'] . " " . $voltage['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $voltage['sensor_descr'] . "\n");
    log_event('Voltage ' . $voltage['sensor_descr'] . " under threshold: " . $volt . " V (< " . $voltage['sensor_limit_low'] . " V)", $device['device_id'], 'voltage', $voltage['sensor_id']);
  }
  else if($voltage['sensor_current'] < $voltage['sensor_limit'] && $volt >= $voltage['sensor_limit']) 
  {
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Voltage Alarm: " . $device['hostname'] . " " . $voltage['sensor_descr'] . " is " . $volt . "V (Limit " . $voltage['sensor_limit'];
    $msg .= "V) at " . date($config['timestamp_format']);
    notify($device, "Voltage Alarm: " . $device['hostname'] . " " . $voltage['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $voltage['sensor_descr'] . "\n");
    log_event('Voltage ' . $voltage['sensor_descr'] . " above threshold: " . $volt . " V (> " . $voltage['sensor_limit'] . " V)", $device['device_id'], 'voltage', $voltage['sensor_id']);
  }
  mysql_query("UPDATE sensor SET sensor_current = '$volt' WHERE sensor_class='voltage' AND sensor_id = '" . $voltage['sensor_id'] . "'");
}

?>
