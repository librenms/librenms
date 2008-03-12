<?

$query = "SELECT * FROM temperature WHERE temp_host = '" . $device['device_id'] . "'";
$temp_data = mysql_query($query);
while($temperature = mysql_fetch_array($temp_data)) {

  $temp_cmd = "snmpget -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " " . $temperature['temp_oid'];
  $temp = `$temp_cmd`;

  echo("Checking temp " . $temperature['temp_descr'] . "... ");

  $temprrd  = addslashes($rrd_dir . "/" . $device['hostname'] . "/temp-" . str_replace("/", "_", str_replace(" ", "_",$temperature['temp_descr'])) . ".rrd");
  $temprrd  = str_replace(")", "_", $temprrd);
  $temprrd  = str_replace("(", "_", $temprrd);

  $otemprrd  = addslashes("rrd/" . $device['hostname'] . "-temp-" . str_replace("/", "_", str_replace(" ", "_",$temperature['temp_descr'])) . ".rrd");
  $otemprrd  = str_replace(")", "_", $otemprrd);
  $otemprrd  = str_replace("(", "_", $otemprrd);

  if(is_file($otemprrd) && !is_file($temprrd)) { rename($otemprrd, $temprrd); echo("Moving $otemprrd to $temprrd");  }

  if (!is_file($temprrd)) {
    `rrdtool create $temprrd \
     --step 300 \
     DS:temp:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400`;
  }

  $temp = trim(str_replace("\"", "", $temp));
  if($temperature['temp_tenths']) { $temp = $temp / 10; }

  echo($temp . "C\n");

  $updatecmd = "rrdtool update $temprrd N:$temp";

  `$updatecmd`;

  if($temperature['temp_current'] < $temperature['temp_limit'] && $temp >= $temperature['temp_limit']) {
    $updated = ", `service_changed` = '" . time() . "' ";
    if($device['sysContact']) { $email = $device['sysContact']; } else { $email = $config['email_default']; }
    $msg  = "Temp Alarm: " . $device['hostname'] . " " . $temperature['temp_descr'] . " is " . $temp . " (Limit " . $temperature['temp_limit'];
    $msg .= ") at " . date('l dS F Y h:i:s A');
    mail($email, "Temp Alarm: " . $device['hostname'] . " " . $temperature['temp_descr'], $msg, $config['email_headers']);
    echo("Alerting for " . $device['hostname'] . " " . $temperature['temp_descr'] . "/n");
  }

  mysql_query("UPDATE temperature SET temp_current = '$temp' WHERE temp_id = '$temperature[temp_id]'");

  

}

?>
