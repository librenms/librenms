<?

$query = "SELECT * FROM temperature WHERE temp_host = '" . $device['device_id'] . "'";
$temp_data = mysql_query($query);
while($temperature = mysql_fetch_array($temp_data)) {

  $temp_cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " " . $temperature['temp_oid'];
  echo($temp_cmd);
  $temp = `$temp_cmd`;

  $temprrd  = addslashes("rrd/" . $device['hostname'] . "-temp-" . str_replace("/", "_", str_replace(" ", "_",$temperature['temp_descr'])) . ".rrd");
  $temprrd  = str_replace(")", "_", $temprrd);
  $temprrd  = str_replace("(", "_", $temprrd);


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
  if($temperature['temp_tenths']) { $temp = $temp / 10; }
  echo("$temprrd N:$temp");

  $updatecmd = "rrdtool update $temprrd N:$temp";

  echo($updatecmd . "\n");

  `$updatecmd`;

  mysql_query("UPDATE temperature SET temp_current = '$temp' WHERE temp_id = '$temperature[temp_id]'");

}

?>
