<?php

$query = "SELECT * FROM processors WHERE device_id = '" . $device['device_id'] . "'";
$proc_data = mysql_query($query);
while($processor = mysql_fetch_array($proc_data)) {

  $proc = snmp_get ($device, $processor['processor_oid'], "-O Uqnv");

  echo("Checking CPU " . $processor['processor_descr'] . "... ");

  $procrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("processor-" . $processor['processor_type'] . "-" . $processor['processor_index'] . ".rrd");

  if (!is_file($procrrd)) {
   shell_exec("rrdtool create $procrrd \
     --step 300 \
     DS:usage:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400");
  }

  $proc = trim(str_replace("\"", "", $proc));
  list($proc) = split(" ", $proc); 

  echo($proc . "%\n");

  $updatecmd = $config['rrdtool'] ." update $procrrd N:$proc";
  shell_exec($updatecmd);

  mysql_query("UPDATE `processors` SET `processor_usage` = '$proc' WHERE `processor_id` = '".$processor['processor_id']."'");

}

?>
