<?php

$query = "SELECT * FROM current WHERE device_id = '" . $device['device_id'] . "'";
$current_data = mysql_query($query);
while($dbcurrent = mysql_fetch_array($current_data)) {

  echo("Checking current " . $dbcurrent['current_descr'] . "... ");

  #$current_cmd = $config['snmpget'] . " -M ".$config['mibdir'] . " -m SNMPv2-MIB -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " " . $dbcurrent['current_oid'] . "|grep -v \"No Such Instance\"";
  #$current = trim(shell_exec($current_cmd));

  $current = snmp_get($device, $dbcurrent['current_oid'], "-OUqnv", "SNMPv2-MIB");

  if ($dbcurrent['current_precision']) 
  {
    $current = $current / $dbcurrent['current_precision'];
  }

  $currentrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("current-" . $dbcurrent['current_descr'] . ".rrd");

  if (!is_file($currentrrd)) {
    `rrdtool create $currentrrd \
     --step 300 \
     DS:current:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400`;
  }

  echo($current . " A\n");

  rrdtool_update($currentrrd,"N:$current");
  
# FIXME also warn when crossing WARN level!!
  if($dbcurrent['current_current'] > $dbcurrent['current_limit_low'] && $current <= $dbcurrent['current_limit_low']) 
  {
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Current Alarm: " . $device['hostname'] . " " . $dbcurrent['current_descr'] . " is " . $current . "A (Limit " . $dbcurrent['current_limit'];
    $msg .= "A) at " . date($config['timestamp_format']);
    mail($email, "Current Alarm: " . $device['hostname'] . " " . $dbcurrent['current_descr'], $msg, $config['email_headers']);
    echo("Alerting for " . $device['hostname'] . " " . $dbcurrent['current_descr'] . "\n");
    log_event('Current ' . $dbcurrent['current_descr'] . " under threshold: " . $current . " A (< " . $dbcurrent['current_limit_low'] . " A)", $device['device_id'], 'current', $current['current_id']);
  }
  else if($dbcurrent['current_current'] < $dbcurrent['current_limit'] && $current >= $dbcurrent['current_limit']) 
  {
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Current Alarm: " . $device['hostname'] . " " . $dbcurrent['current_descr'] . " is " . $current . "A (Limit " . $dbcurrent['current_limit'];
    $msg .= "A) at " . date($config['timestamp_format']);
    mail($email, "Current Alarm: " . $device['hostname'] . " " . $dbcurrent['current_descr'], $msg, $config['email_headers']);
    echo("Alerting for " . $device['hostname'] . " " . $dbcurrent['current_descr'] . "\n");
    log_event('Current ' . $dbcurrent['current_descr'] . " above threshold: " . $current . " A (> " . $dbcurrent['current_limit'] . " A)", $device['device_id'], 'current', $current['current_id']);
  }

  mysql_query("UPDATE current SET current_current = '$current' WHERE current_id = '" . $dbcurrent['current_id'] . "'");
}

?>
