<?php

  unset( $storage_exists );

  echo("HOST-RESOURCES-MIB Storage : ");  

  $oids = shell_exec($config['snmpwalk'] . " -CI -m HOST-RESOURCES-MIB -Osq -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " hrStorageIndex");
  $oids = trim(str_replace("hrStorageIndex.","",$oids));

  foreach(explode("\n", $oids) as $data) {
   if($data) {
    $data = trim($data);
    list($oid,$hrStorageIndex) = explode(" ", $data);
    $temp = shell_exec($config['snmpget'] . " -m HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " hrStorageDescr.$oid hrStorageAllocationUnits.$oid hrStorageSize.$oid hrStorageType.$oid");
    $temp = trim($temp);
    list($descr, $units, $size, $fstype) = explode("\n", $temp);
    list($units) = explode(" ", $units);
    $allow = 1;
    foreach($config['ignore_mount'] as $bi) { if($bi == $descr) { $allow = 0; } }
    foreach($config['ignore_mount_string'] as $bi) { if(strpos($descr, $bi) !== FALSE) { $allow = 0; } else { echo("$descr -> $bi \n"); } }
    foreach($config['ignore_mount_regexp'] as $bi) { if(preg_match($bi, $descr)) { $allow = 0; } }
    $descr = str_replace("mounted on: ", "", $descr);
    $descr = str_replace(": var file system", "", $descr);

    if((strstr($fstype, "FixedDisk") || strstr($fstype, "Ram") || strstr($fstype, "VirtualMemory")) && $size > '0' && $allow) {
      if(mysql_result(mysql_query("SELECT count(storage_id) FROM `storage` WHERE hrStorageIndex = '$hrStorageIndex' AND host_id = '".$device['device_id']."'"),0) == '0') {
        $query  = "INSERT INTO storage (`host_id`, `hrStorageIndex`, `hrStorageType`, `hrStorageDescr`,`hrStorageSize`,`hrStorageAllocationUnits`) ";
        $query .= "values ('".$device['device_id']."', '$hrStorageIndex', '$fstype', '$descr', '$size', '$units')";
	mysql_query($query);
	echo("+");
      } else {
        $data = mysql_fetch_array(mysql_query("SELECT * FROM `storage` WHERE hrStorageIndex = '$hrStorageIndex' AND host_id = '".$device['device_id']."'"));
	if($data['hrStorageDescr'] != $descr || $data['hrStorageSize'] != $size || $data['hrStorageAllocationUnits'] != $units ) {
          $query  = "UPDATE storage SET `hrStorageDescr` = '$descr', `hrStorageType` = '$fstype', `hrStorageSize` = '$size', `hrStorageAllocationUnits` = '$units' ";
          $query .= "WHERE hrStorageIndex = '$hrStorageIndex' AND host_id = '".$device['device_id']."'";
	  echo("U");
	  mysql_query($query);
        } else { echo("."); }
      }
      $storage_exists[] = $device[device_id]." $hrStorageIndex";
    } else { echo("X"); };
   }
  }

$sql = "SELECT * FROM storage AS S, devices AS D where S.host_id = D.device_id AND D.device_id = '".$device['device_id']."'";
$query = mysql_query($sql);

while ($store = mysql_fetch_array($query)) {
        unset($exists);
        $i = 0;
        while ($i < count($storage_exists) && !$exists) {
            $thisstore = $store['host_id'] . " " . $store['hrStorageIndex'];
            if ($storage_exists[$i] == $thisstore) { $exists = 1; }
            $i++;
        }
        if(!$exists) {
          echo("-");
          mysql_query("DELETE FROM storage WHERE storage_id = '" . $store['storage_id'] . "'");
        }
}

echo("\n");


?>
