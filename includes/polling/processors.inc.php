<?php

$query = "SELECT * FROM processors WHERE device_id = '" . $device['device_id'] . "'";
$proc_data = mysql_query($query);
while ($processor = mysql_fetch_array($proc_data))
{
  echo("Processor " . $processor['processor_descr'] . "... ");

  $file = $config['install_dir']."/includes/polling/processors-".$processor['processor_type'].".inc.php";
  if (is_file($file))
  {
    include($file);
  } else {
    $proc = snmp_get ($device, $processor['processor_oid'], "-O Uqnv");
  }

  $procrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("processor-" . $processor['processor_type'] . "-" . $processor['processor_index'] . ".rrd");

  if (!is_file($procrrd))
  {
    rrdtool_create($procrrd, "--step 300 \
     DS:usage:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400");
  }

  $proc = trim(str_replace("\"", "", $proc));
  list($proc) = preg_split("@\ @", $proc);

  if (!$processor['processor_precision']) { $processor['processor_precision'] = "1"; };
  $proc = round($proc / $processor['processor_precision'],2);

  echo($proc . "%\n");

  rrdtool_update($procrrd,"N:$proc");

  mysql_query("UPDATE `processors` SET `processor_usage` = '$proc' WHERE `processor_id` = '".$processor['processor_id']."'");
}

?>