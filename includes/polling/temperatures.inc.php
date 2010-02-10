<?php

$query = "SELECT * FROM temperature WHERE temp_host = '" . $device['device_id'] . "'";
$temp_data = mysql_query($query);
while($temperature = mysql_fetch_array($temp_data)) {

  echo("Checking temp " . $temperature['temp_descr'] . "... ");

  for ($i = 0;$i < 5;$i++) # Try 5 times to get a valid temp reading;
  {
    if ($debug) echo "Attempt $i ";
    $temp_cmd = $config['snmpget'] . " -m SNMPv2-MIB -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " " . $temperature['temp_oid'] . "|grep -v \"No Such Instance\"";
    $temp = trim(str_replace("\"", "", shell_exec($temp_cmd)));

    if ($temp != 9999) break; # TME sometimes sends 999.9 when it is right in the middle of an update;
  }

  if($temperature['temp_tenths']) { $temp = $temp / 10; }
  else
  {
    if ($temperature['temp_precision']) { $temp = $temp / $temperature['temp_precision']; }
  }
  #FIXME also divide the limit here

  $temprrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("temp-" . $temperature['temp_descr'] . ".rrd");

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

  if($temperature['temp_current'] < $temperature['temp_limit'] && $temp >= $temperature['temp_limit']) {
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Temp Alarm: " . $device['hostname'] . " " . $temperature['temp_descr'] . " is " . $temp . " (Limit " . $temperature['temp_limit'];
    $msg .= ") at " . date('l dS F Y h:i:s A');
    mail($email, "Temp Alarm: " . $device['hostname'] . " " . $temperature['temp_descr'], $msg, $config['email_headers']);
    echo("Alerting for " . $device['hostname'] . " " . $temperature['temp_descr'] . "\n");
    eventlog('Temperature ' . $temperature['temp_descr'] . " over threshold: " . $temperature['temp_current'] . " &deg;C (&gt; " . $temperature['temp_limit'] . " &deg;C)", $device['device_id']);
  }

  mysql_query("UPDATE temperature SET temp_current = '$temp' WHERE temp_id = '" . $temperature['temp_id'] . "'");
}

?>
