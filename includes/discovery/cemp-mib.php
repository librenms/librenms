<?php
  $id = $device['device_id'];
  $hostname = $device['hostname'];
  $community = $device['community'];
  $snmpver = $device['snmpver'];
  $port = $device['port'];

  echo("CISCO-ENHANCED-MEMORY-POOL : ");

  ## Cisco Enhanced Mempool
  if($device['os_type'] == "ios") {

    $oids = shell_exec($config['snmpwalk'] . " -m CISCO-ENHANCED-MEMPOOL-MIB -v2c -CI -Osq -c ".$community." ".$hostname.":".$port." cempMemPoolName | sed s/cempMemPoolName.//g");
    $oids = trim($oids);
    foreach(explode("\n", $oids) as $data) {
     $data = trim($data);
     if($data) {
      list($oid, $cempMemPoolName) = explode(" ", $data);
      list($entPhysicalIndex, $Index) = explode(".", $oid);
      $cempMemPoolType = trim(shell_exec($config['snmpget'] . " -m CISCO-ENHANCED-MEMPOOL-MIB -O qv -v2c -c $community $hostname:$port cempMemPoolType.$oid"));
      $cempMemPoolValid = trim(shell_exec($config['snmpget'] . " -m CISCO-ENHANCED-MEMPOOL-MIB -O qv -v2c -c $community $hostname:$port cempMemPoolValid.$oid"));
      if(!strstr($descr, "No") && !strstr($usage, "No") && $cempMemPoolName != "" ) {
        $descr = str_replace("\"", "", $descr);
        $descr = trim($descr);
        #echo("[$cempMemPoolName ($oid)] ");
        if(mysql_result(mysql_query("SELECT count(cempMemPool_id) FROM `cempMemPool` WHERE `Index` = '$Index'  AND `entPhysicalIndex` = '$entPhysicalIndex' AND `device_id` = '$id'"),0) == '0') {
          $query = "INSERT INTO cempMemPool (`Index`, `entPhysicalIndex`, `cempMemPoolType`, `cempMemPoolName`, `cempMemPoolValid`, `device_id`) 
                                      values ('$Index', '$entPhysicalIndex', '$cempMemPoolType', '$cempMemPoolName', '$cempMemPoolValid', '$id')";
          mysql_query($query);
          #echo("$query\n");
          echo("+");
        } else { echo("."); }
        $valid_cpm[$id][$oid] = 1;
      }
     }
    } 
  } ## End Cisco Enhanced Mempool

  ##### ************FIX ME*********** 
  ##### WRITE CODE TO REMOVE OLD RAMS
  ##### SOMETIMES THEY CAN GET STOLED
  ##### *****************************

echo("\n");

?>
