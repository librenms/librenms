<?php

$query = "SELECT * FROM fanspeed WHERE device_id = '" . $device['device_id'] . "'";
$fan_data = mysql_query($query);
while($fanspeed = mysql_fetch_array($fan_data)) {

  echo("Checking fan " . $fanspeed['fan_descr'] . "... ");

  $fan_cmd = $config['snmpget'] . " -m SNMPv2-MIB -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " " . $fanspeed['fan_oid'] . "|grep -v \"No Such Instance\"";
  $fan = trim(str_replace("\"", "", shell_exec($fan_cmd)));
  if ($fanspeed['fan_precision']) { $fan = $fan / $fanspeed['fan_precision']; }
  #FIXME also divide the limit here

  $fanrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("fan-" . $fanspeed['fan_descr'] . ".rrd");

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

  if($fanspeed['fan_current'] > $fanspeed['fan_limit'] && $fan <= $fanspeed['fan_limit']) {
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Fan Alarm: " . $device['hostname'] . " " . $fanspeed['fan_descr'] . " is " . $fan . "rpm (Limit " . $fanspeed['fan_limit'];
    $msg .= "rpm) at " . date($config['timestamp_format']);
    mail($email, "Fan Alarm: " . $device['hostname'] . " " . $fanspeed['fan_descr'], $msg, $config['email_headers']);
    echo("Alerting for " . $device['hostname'] . " " . $fanspeed['fan_descr'] . "\n");
    log_event('Fan speed ' . $fanspeed['fan_descr'] . " under threshold: " . $fanspeed['fan_current'] . " rpm (&gt; " . $fanspeed['fan_limit'] . " rpm)", $device['device_id'], 'fanspeed', $fanspeed['fan_id']);
  }

  mysql_query("UPDATE fanspeed SET fan_current = '$fan' WHERE fan_id = '" . $fanspeed['fan_id'] . "'");
}

?>
