<?php

$query = "SELECT * FROM sensors WHERE sensor_class='temperature' AND device_id = '" . $device['device_id'] . "'";
$temp_data = mysql_query($query);
while($temperature = mysql_fetch_array($temp_data)) {

  echo("Checking temp " . $temperature['sensor_descr'] . "... ");

  for ($i = 0;$i < 5;$i++) # Try 5 times to get a valid temp reading
  {
    if ($debug) echo "Attempt $i ";
    $temp_cmd = $config['snmpget'] . " -m SNMPv2-MIB -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " " . $temperature['sensor_oid'] . "|grep -v \"No Such Instance\"";
    $temp = trim(str_replace("\"", "", shell_exec($temp_cmd)));

    if ($temp != 9999) break; # TME sometimes sends 999.9 when it is right in the middle of an update;
    sleep(1); # Give the TME some time to reset
  }

  if ($temperature['sensor_precision']) { $temp = $temp / $temperature['sensor_precision']; }

  $temprrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("temp-" . $temperature['sensor_descr'] . ".rrd");

  if (!is_file($temprrd)) {
    `rrdtool create $temprrd \
     --step 300 \
     DS:temp:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400`;
  }

  echo($temp . "C\n");

  rrdtool_update($temprrd,"N:$temp");

  if($temperature['sensor_current'] < $temperature['sensor_limit'] && $temp >= $temperature['sensor_limit']) {
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Temp Alarm: " . $device['hostname'] . " " . $temperature['sensor_descr'] . " is " . $temp . " (Limit " . $temperature['sensor_limit'];
    $msg .= ") at " . date($config['timestamp_format']);
    mail($email, "Temp Alarm: " . $device['hostname'] . " " . $temperature['sensor_descr'], $msg, $config['email_headers']);
    echo("Alerting for " . $device['hostname'] . " " . $temperature['sensor_descr'] . "\n");
    log_event('Temperature ' . $temperature['sensor_descr'] . " over threshold: " . $temp . " °C (> " . $temperature['sensor_limit'] . " °C)", $device['device_id'], 'temperature', $temperature['sensor_id']);
  }

  mysql_query("UPDATE sensors SET sensor_current = '$temp' WHERE sensor_class='temperature' AND sensor_id = '" . $temperature['sensor_id'] . "'");
}

?>
