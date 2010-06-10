<?php

$query = "SELECT * FROM frequency WHERE device_id = '" . $device['device_id'] . "'";
$freq_data = mysql_query($query);
while($frequency = mysql_fetch_array($freq_data)) {

  echo("Checking frequency " . $frequency['freq_descr'] . "... ");

  $freq_cmd = $config['snmpget'] . " -m SNMPv2-MIB -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " " . $frequency['freq_oid'] . "|grep -v \"No Such Instance\"";
  $freq = trim(str_replace("\"", "", shell_exec($freq_cmd)));

  if ($frequency['freq_precision']) 
  {
    $freq = $freq / $frequency['freq_precision'];
  }

  $freqrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("freq-" . $frequency['freq_descr'] . ".rrd");

  if (!is_file($freqrrd)) {
    `rrdtool create $freqrrd \
     --step 300 \
     DS:freq:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400`;
  }

  echo($freq . " Hz\n");

  rrdtool_update($freqrrd,"N:$freq");

  if($frequency['freq_current'] > $frequency['freq_limit_low'] && $freq <= $frequency['freq_limit_low']) 
  {
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Frequency Alarm: " . $device['hostname'] . " " . $frequency['freq_descr'] . " is " . $freq . "Hz (Limit " . $frequency['freq_limit'];
    $msg .= "Hz) at " . date($config['timestamp_format']);
    mail($email, "Frequency Alarm: " . $device['hostname'] . " " . $frequency['freq_descr'], $msg, $config['email_headers']);
    echo("Alerting for " . $device['hostname'] . " " . $frequency['freq_descr'] . "\n");
    eventlog('Frequency ' . $frequency['freq_descr'] . " under threshold: " . $freq . " Hz (< " . $frequency['freq_limit_low'] . " Hz)", $device['device_id']);
  }
  else if($frequency['freq_current'] < $frequency['freq_limit'] && $freq >= $frequency['freq_limit']) 
  {
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Frequency Alarm: " . $device['hostname'] . " " . $frequency['freq_descr'] . " is " . $freq . "Hz (Limit " . $frequency['freq_limit'];
    $msg .= "Hz) at " . date($config['timestamp_format']);
    mail($email, "Frequency Alarm: " . $device['hostname'] . " " . $frequency['freq_descr'], $msg, $config['email_headers']);
    echo("Alerting for " . $device['hostname'] . " " . $frequency['freq_descr'] . "\n");
    eventlog('Frequency ' . $frequency['freq_descr'] . " above threshold: " . $freq . " Hz (> " . $frequency['freq_limit'] . " Hz)", $device['device_id']);
  }

  mysql_query("UPDATE frequency SET freq_current = '$freq' WHERE freq_id = '" . $frequency['freq_id'] . "'");
}

?>
