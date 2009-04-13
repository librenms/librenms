<?

$query = "SELECT * FROM cpmCPU WHERE device_id = '" . $device['device_id'] . "'";
$proc_data = mysql_query($query);
while($processor = mysql_fetch_array($proc_data)) {

  $proc_cmd = "snmpget -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " cpmCPUTotal5minRev." . $processor['cpmCPU_oid'];
  $proc = shell_exec($proc_cmd);

  echo("Checking CPU " . $processor['entPhysicalDescr'] . "... ");

  $procrrd  = addslashes($config['rrd_dir'] . "/" . $device['hostname'] . "/cpmCPU-" . $processor['cpmCPU_oid'] . ".rrd");

  if (!is_file($procrrd)) {
    `rrdtool create $procrrd \
     --step 300 \
     DS:usage:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400`;
  }

  $proc = trim(str_replace("\"", "", $proc));
  list($proc) = split(" ", $proc); 

  echo($proc . "%\n");

  $updatecmd = $config['rrdtool'] ." update $procrrd N:$proc";
  echo("$updatecmd");
  shell_exec($updatecmd);

  mysql_query("UPDATE `cpmCPU` SET `cpmCPUTotal5minRev` = '$proc' WHERE `cpmCPU_id` = '".$processor['cpmCPU_id']."'");

}

?>
