<?php

echo("CISCO-MEMORY-POOL: ");

$query = "SELECT * FROM cmpMemPool WHERE device_id = '" . $device['device_id'] . "'";
$pool_data = mysql_query($query);
while($mempool = mysql_fetch_array($pool_data)) {

  echo($mempool['cmpName'] . " ");

  $oid = $mempool['Index']; 

  $pool_cmd  = $config['snmpget'] . " -m CISCO-MEMORY-POOL-MIB -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
  $pool_cmd .= " ciscoMemoryPoolUsed.$oid ciscoMemoryPoolFree.$oid ciscoMemoryPoolLargestFree.$oid";
  $pool_cmd .= " | cut -f 1 -d ' '";

  $pool = shell_exec($pool_cmd);

  list($cmpUsed, $cmpFree, $cmpLargestFree) = explode("\n", $pool);

  echo(round(($cmpUsed/($cmpFree+$cmpUsed))*100) . "% ");

  $poolrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/cmp-" . $mempool['Index'] . ".rrd";

  if (!is_file($poolrrd)) {
    shell_exec ($config['rrdtool'] . " create $poolrrd \
     --step 300 \
      DS:used:GAUGE:600:0:U \
      DS:free:GAUGE:600:-1:U \
      DS:largestfree:GAUGE:600:0:U \
      RRA:AVERAGE:0.5:1:2000 \
      RRA:AVERAGE:0.5:6:2000 \
      RRA:AVERAGE:0.5:24:2000 \
      RRA:AVERAGE:0.5:288:2000 \
      RRA:MAX:0.5:1:2000 \
      RRA:MAX:0.5:6:2000 \
      RRA:MAX:0.5:24:2000 \
      RRA:MAX:0.5:288:2000");
  }

  $pool = trim(str_replace("\"", "", $pool));
  list($pool) = split(" ", $pool); 

  $updatecmd = $config['rrdtool'] ." update $poolrrd N:$cmpUsed:$cmpFree:$cmpLargestFree";
  shell_exec($updatecmd);

  $update_query = "UPDATE `cmpMemPool` SET cmpUsed='$cmpUsed', cmpFree='$cmpFree', cmpLargestFree='$cmpLargestFree' WHERE `cmp_id` = '".$mempool['cmp_id']."'";
  mysql_query($update_query);

}

echo("\n");

?>
