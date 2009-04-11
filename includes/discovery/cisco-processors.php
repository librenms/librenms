<?
  $id = $device['device_id'];
  $hostname = $device['hostname'];
  $community = $device['community'];
  $snmpver = $device['snmpver'];
  $port = $device['port'];

  echo("Cisco Procs : ");

  ## Cisco Processors
  if($device['os'] == "IOS" || $device['os'] == "IOS XE") {
    echo("Cisco ");
    $oids = shell_exec($config['snmpwalk'] . " -v2c -CI -Osqn -c ".$community." ".$hostname.":".$port." .1.3.6.1.4.1.9.9.109.1.1.1.1.2 | sed s/.1.3.6.1.4.1.9.9.109.1.1.1.1.2.//g");
    $oids = trim($oids);
    foreach(explode("\n", $oids) as $data) {
     $data = trim($data);
     if($data) {
      list($oid, $entPhysicalIndex) = explode(" ", $data);
      $usage_oid = "cpmCPUTotal5minRev.$oid";
      $descr_oid = "entPhysicalName.$entPhysicalIndex";
      $descr = trim(shell_exec("snmpget -O qv -v2c -c $community $hostname:$port $descr_oid"));
      $usage = trim(shell_exec("snmpget -O qv -v2c -c $community $hostname:$port $usage_oid"));
      if($entPhysicalIndex == "0") { $descr = "Proc $oid"; }
      if(!strstr($descr, "No") && !strstr($usage, "No") && $descr != "" ) {
        $descr = str_replace("\"", "", $descr);
        $descr = str_replace("CPU of ", "", $descr);
	$descr = str_replace("Sub-", "", $descr);
        $descr = str_replace("Routing Processor", "RP", $descr);
        $descr = str_replace("Switching Processor", "SP", $descr);
        $descr = trim($descr);
#        echo("[$descr ($oid)] ");
        if(mysql_result(mysql_query("SELECT count(cpmCPU_id) FROM `cpmCPU` WHERE `cpmCPU_oid` = '$oid' AND `device_id` = '$id'"),0) == '0') {
          $query = "INSERT INTO cpmCPU (`entPhysicalIndex`, `device_id`, `entPhysicalDescr`, `cpmCPU_oid`) values ('$entPhysicalIndex', '$id', '$descr', '$oid')";
          mysql_query($query);
#	  echo("$query");
          echo("+");
        } else { echo("."); }
        $valid_cpm[$id][$oid] = 1;
      }
     }
    } 
  } ## End Cisco Processors


##### ************FIX ME***************** 
##### WRITE CODE TO REMOVE OLD PROCESSORS
##### SOMETIMES CPUS FALL OUT, DONT THEY?
##### ***********************************

?>
