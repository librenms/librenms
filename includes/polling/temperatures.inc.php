<?php

$query = "SELECT * FROM sensors WHERE sensor_class='temperature' AND device_id = '" . $device['device_id'] . "' AND poller_type='snmp'";
$temp_data = mysql_query($query);
while($temperature = mysql_fetch_array($temp_data)) {

  echo("Checking temp " . $temperature['sensor_descr'] . "... ");

  for ($i = 0;$i < 5;$i++) # Try 5 times to get a valid temp reading
  {
    if ($debug) echo("Attempt $i ");
    #FIXME snmp_get
    $temp_cmd = $config['snmpget'] . " -M ".$config['mibdir'] . " -m SNMPv2-MIB -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " " . $temperature['sensor_oid'] . "|grep -v \"No Such Instance\"";
    $temp = trim(str_replace("\"", "", shell_exec($temp_cmd)));

    if ($temp != 9999) break; # TME sometimes sends 999.9 when it is right in the middle of an update;
    sleep(1); # Give the TME some time to reset
  }

  if ($temperature['sensor_divisor'])    { $temp = $temp / $temperature['sensor_divisor']; }
  if ($temperature['sensor_multiplier']) { $temp = $temp * $temperature['sensor_multiplier']; }

  $old_rrd_file  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("temperature-" . $temperature['sensor_descr'] . ".rrd");
  $rrd_file = $config['rrd_dir'] . "/" . $device['hostname'] . "/temperature-" . safename($temperature['sensor_type']."-".$temperature['sensor_index']) . ".rrd";

  if(is_file($old_rrd_file)) { rename($old_rrd_file, $rrd_file); }

  if (!is_file($rrd_file)) {
    `rrdtool create $rrd_file \
     --step 300 \
     DS:sensor:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:600 \
     RRA:AVERAGE:0.5:6:700 \
     RRA:AVERAGE:0.5:24:775 \
     RRA:AVERAGE:0.5:288:797 \
     RRA:MAX:0.5:1:600 \
     RRA:MAX:0.5:6:700 \
     RRA:MAX:0.5:24:775 \
     RRA:MAX:0.5:288:797\
     RRA:MIN:0.5:1:600 \
     RRA:MIN:0.5:6:700 \
     RRA:MIN:0.5:24:775 \
     RRA:MIN:0.5:288:797`;
  }

  echo($temp . "C\n");

  rrdtool_update($rrd_file,"N:$temp");

  if($temperature['sensor_current'] < $temperature['sensor_limit'] && $temp >= $temperature['sensor_limit']) 
  {
    $msg  = "Temp Alarm: " . $device['hostname'] . " " . $temperature['sensor_descr'] . " is " . $temp . " (Limit " . $temperature['sensor_limit'];
    $msg .= ") at " . date($config['timestamp_format']);
    notify($device, "Temp Alarm: " . $device['hostname'] . " " . $temperature['sensor_descr'], $msg);
    echo("Alerting for " . $device['hostname'] . " " . $temperature['sensor_descr'] . "\n");
    log_event('Temperature ' . $temperature['sensor_descr'] . " over threshold: " . $temp . " " . html_entity_decode('&deg;') . "C (>= " . $temperature['sensor_limit'] . " " . html_entity_decode('&deg;') . 'C)', $device['device_id'], 'temperature', $temperature['sensor_id']);
  }

  mysql_query("UPDATE sensors SET sensor_current = '$temp' WHERE sensor_class='temperature' AND sensor_id = '" . $temperature['sensor_id'] . "'");
}

?>
