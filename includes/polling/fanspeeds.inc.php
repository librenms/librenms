<?php

$query = "SELECT * FROM sensors WHERE sensor_class='fanspeed' AND device_id = '" . $device['device_id'] . "'";
$fan_data = mysql_query($query);
while($fanspeed = mysql_fetch_array($fan_data)) {

  echo("Checking fan " . $fanspeed['sensor_descr'] . "... ");

  #$fan_cmd = $config['snmpget'] . " -M ".$config['mibdir'] . " -m SNMPv2-MIB -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " " . $fanspeed['sensor_oid'] . "|grep -v \"No Such Instance\"";
  #$fan = trim(str_replace("\"", "", shell_exec($fan_cmd)));

  $fan = snmp_get($device, $fanspeed['sensor_oid'], "-OUqnv", "SNMPv2-MIB");

  if ($fanspeed['sensor_precision']) { $fan = $fan / $fanspeed['sensor_precision']; }

  $fanrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("fan-" . $fanspeed['sensor_descr'] . ".rrd");

  if (!is_file($fanrrd)) {
     `rrdtool create $fanrrd \
     --step 300 \
     DS:fan:GAUGE:600:0:20000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400`;
  }

  echo($fan . " rpm\n");

  rrdtool_update($fanrrd,"N:$fan");

  if($fanspeed['sensor_current'] > $fanspeed['sensor_limit'] && $fan <= $fanspeed['sensor_limit']) {
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Fan Alarm: " . $device['hostname'] . " " . $fanspeed['sensor_descr'] . " is " . $fan . "rpm (Limit " . $fanspeed['sensor_limit'];
    $msg .= "rpm) at " . date($config['timestamp_format']);
    mail($email, "Fan Alarm: " . $device['hostname'] . " " . $fanspeed['sensor_descr'], $msg, $config['email_headers']);
    echo("Alerting for " . $device['hostname'] . " " . $fanspeed['sensor_descr'] . "\n");
    log_event('Fan speed ' . $fanspeed['sensor_descr'] . " under threshold: " . $fanspeed['sensor_current'] . " rpm (&gt; " . $fanspeed['sensor_limit'] . " rpm)", $device['device_id'], 'fanspeed', $fanspeed['sensor_id']);
  }

  mysql_query("UPDATE sensors SET sensor_current = '$fan' WHERE sensor_class='fanspeed' AND sensor_id = '" . $fanspeed['sensor_id'] . "'");
}

?>
