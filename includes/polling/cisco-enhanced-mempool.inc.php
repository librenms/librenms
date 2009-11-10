<?php

echo("CISCO-ENHANCED-MEMPOOL: ");

$query = "SELECT * FROM cempMemPool WHERE device_id = '" . $device['device_id'] . "'";
$pool_data = mysql_query($query);
while($mempool = mysql_fetch_array($pool_data)) {

  $entPhysicalName = @mysql_result(mysql_query("SELECT `entPhysicalName` from entPhysical WHERE device_id = '".$device['device_id']."'
                                               AND `entPhysicalIndex` = '".$mempool['entPhysicalIndex']."'"),0);

  echo($entPhysicalName . " - " . $mempool['cempMemPoolName'] . " ");


  $oid = $mempool['entPhysicalIndex'] . "." . $mempool['Index']; 

  $pool_cmd  = "snmpget -m CISCO-ENHANCED-MEMPOOL-MIB -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
  $pool_cmd .= " cempMemPoolUsed.$oid cempMemPoolFree.$oid cempMemPoolLargestFree.$oid cempMemPoolLowestFree.$oid";
  $pool_cmd .= " | cut -f 1 -d ' '";

  $pool = shell_exec($pool_cmd);

  list($cempMemPoolUsed, $cempMemPoolFree, $cempMemPoolLargestFree, $cempMemPoolLowestFree) = explode("\n", $pool);

  echo(round(($cempMemPoolUsed/($cempMemPoolFree+$cempMemPoolUsed))*100) . "% ");


  $poolrrd  = addslashes($config['rrd_dir'] . "/" . $device['hostname'] . "/cempMemPool-" . $oid . ".rrd");

  if (!is_file($poolrrd)) {
    `rrdtool create $poolrrd \
     --step 300 \
      DS:used:GAUGE:600:0:U \
      DS:free:GAUGE:600:-1:U \
      DS:largestfree:GAUGE:600:0:U \
      DS:lowestfree:GAUGE:600:-1:U \
      RRA:AVERAGE:0.5:1:2000 \
      RRA:AVERAGE:0.5:6:2000 \
      RRA:AVERAGE:0.5:24:2000 \
      RRA:AVERAGE:0.5:288:2000 \
      RRA:MAX:0.5:1:2000 \
      RRA:MAX:0.5:6:2000 \
      RRA:MAX:0.5:24:2000 \
      RRA:MAX:0.5:288:2000`;
  }

  $pool = trim(str_replace("\"", "", $pool));
  list($pool) = split(" ", $pool); 

  $updatecmd = $config['rrdtool'] ." update $poolrrd N:$cempMemPoolUsed:$cempMemPoolFree:$cempMemPoolLargestFree:$cempMemPoolLowestFree";
  shell_exec($updatecmd);

  $update_query = "UPDATE `cempMemPool` SET cempMemPoolUsed='$cempMemPoolUsed', cempMemPoolFree='$cempMemPoolFree', cempMemPoolLargestFree='$cempMemPoolLargestFree', cempMemPoolLowestFree='$cempMemPoolLowestFree' WHERE `cempMemPool_id` = '".$mempool['cempMemPool_id']."'";
  mysql_query($update_query);

}

echo("\n");

?>
