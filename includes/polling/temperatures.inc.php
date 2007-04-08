<?

$query = "SELECT * FROM temperature WHERE temp_host = '" . $device['device_id'] . "'";
$temp_data = mysql_query($query);
while($temperature = mysql_fetch_array($temp_data)) {

  $temp_cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " " . $temperature['temp_oid'];
  echo($temp_cmd);
  $temp = `$temp_cmd`;

  $temprrd  = "rrd/" . $device[hostname] . "-temp-" . $temperature[temp_id] . ".rrd";
  $temprrdold  = "rrd/" . $device[hostname] . "-temp" . $temperature[temp_id] . ".rrd";
  if (is_file($temprrdold)) { 
    rename($temprrdold, $temprrd); 
  }

  if (!is_file($temprrd)) {
    `rrdtool create $temprrd \
     --step 300 \
     DS:temp:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400`;
  }

  $temp = str_replace("\"", "", $temp);
  echo("$temprrd, N:$temp");

  `rrdtool update $temprrd N:$temp`;

  mysql_query("UPDATE temperature SET temp_current = '$temp' WHERE temp_id = '$temperature[temp_id]'");

}

?>
