<?

$query = "SELECT * FROM temperature AS T temp_host = '" . $device[id] . "'";
$temp_data = mysql_query($query);
while($temperature = mysql_fetch_array($temp_data)) {

  $community = $temperature[community];
  $hostname = $temperature[hostname];
  $snmpver = $temperature[snmpver];

  $temp_cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " " . $temperature['temp_oid'];
  $temp = `$temp_cmd`;

  $temprrd  = "rrd/" . $temperature[hostname] . "-temp" . $temperature[temp_id] . ".rrd";
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
