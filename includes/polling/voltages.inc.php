<?php

$query = "SELECT * FROM voltage WHERE device_id = '" . $device['device_id'] . "'";
$volt_data = mysql_query($query);
while($voltage = mysql_fetch_array($volt_data)) {

  echo("Checking voltage " . $voltage['volt_descr'] . "... ");

  $volt_cmd = $config['snmpget'] . " -m SNMPv2-MIB -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " " . $voltage['volt_oid'] . "|grep -v \"No Such Instance\"";
  $volt = trim(str_replace("\"", "", shell_exec($volt_cmd)));

  if ($voltage['volt_precision']) 
  {
    $volt = $volt / $voltage['volt_precision'];
  }

  $voltrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("volt-" . $voltage['volt_descr'] . ".rrd");

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

  if($voltage['volt_current'] > $voltage['volt_limit_low'] && $volt <= $voltage['volt_limit_low']) 
  {
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Voltage Alarm: " . $device['hostname'] . " " . $voltage['volt_descr'] . " is " . $volt . "V (Limit " . $voltage['volt_limit'];
    $msg .= "V) at " . date($config['timestamp_format']);
    mail($email, "Voltage Alarm: " . $device['hostname'] . " " . $voltage['volt_descr'], $msg, $config['email_headers']);
    echo("Alerting for " . $device['hostname'] . " " . $voltage['volt_descr'] . "\n");
    log_event('Voltage ' . $voltage['volt_descr'] . " under threshold: " . $volt . " V (< " . $voltage['volt_limit_low'] . " V)", $device['device_id'], 'voltage', $voltage['volt_id']);
  }
  else if($voltage['volt_current'] < $voltage['volt_limit'] && $volt >= $voltage['volt_limit']) 
  {
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Voltage Alarm: " . $device['hostname'] . " " . $voltage['volt_descr'] . " is " . $volt . "V (Limit " . $voltage['volt_limit'];
    $msg .= "V) at " . date($config['timestamp_format']);
    mail($email, "Voltage Alarm: " . $device['hostname'] . " " . $voltage['volt_descr'], $msg, $config['email_headers']);
    echo("Alerting for " . $device['hostname'] . " " . $voltage['volt_descr'] . "\n");
    log_event('Voltage ' . $voltage['volt_descr'] . " above threshold: " . $volt . " V (> " . $voltage['volt_limit'] . " V)", $device['device_id'], 'voltage', $voltage['volt_id']);
  }

  mysql_query("UPDATE voltage SET volt_current = '$volt' WHERE volt_id = '" . $voltage['volt_id'] . "'");
}

?>
