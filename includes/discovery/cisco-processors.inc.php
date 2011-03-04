<?php
  $id = $device['device_id'];
  $hostname = $device['hostname'];
  $community = $device['community'];
  $snmpver = $device['snmpver'];
  $port = $device['port'];

  echo("CISCO-PROCESS-MIB : ");

  ## Cisco Processors
  if($device['os'] == "ios") {
    /* FIXME: switch to snmp.inc.php:snmp_walk() */
    $oids = shell_exec($config['snmpwalk'] . " -M " . $config['mibdir'] . " -m CISCO-PROCESS-MIB -".$device['snmpver']." -CI -Osqn -c ".$community." ".$protocol.":".$hostname.":".$port." .1.3.6.1.4.1.9.9.109.1.1.1.1.2 | sed s/.1.3.6.1.4.1.9.9.109.1.1.1.1.2.//g");
    $oids = trim($oids);
    foreach(explode("\n", $oids) as $data) {
     $data = trim($data);
     if($data) {
      list($oid, $entPhysicalIndex) = explode(" ", $data);
      $usage_oid = "cpmCPUTotal5minRev.$oid";
      $descr_oid = "entPhysicalName.$entPhysicalIndex";
      $descr = snmp_get($device, $descr_oid, "-O qv", "ENTITY-MIB", $config['mibdir']);
      $usage = snmp_get($device, $usage_oid, "-O qv", "CISCO-PROCESS-MIB", $config['mibdir']);
      if($entPhysicalIndex == "0") { $descr = "Proc $oid"; }
      if(!strstr($descr, "No") && !strstr($usage, "No") && $descr != "" ) {
        $descr = str_replace("\"", "", $descr);
        $descr = str_replace("CPU of ", "", $descr);
	$descr = str_replace("Sub-", "", $descr);
        $descr = str_replace("Routing Processor", "RP", $descr);
        $descr = str_replace("Switching Processor", "SP", $descr);
        $descr = trim($descr);
        if(mysql_result(mysql_query("SELECT count(cpmCPU_id) FROM `cpmCPU` WHERE `cpmCPU_oid` = '$oid' AND `device_id` = '$id'"),0) == '0') {
          $query = "INSERT INTO cpmCPU (`entPhysicalIndex`, `device_id`, `entPhysicalDescr`, `cpmCPU_oid`) values ('$entPhysicalIndex', '$id', '$descr', '$oid')";
          mysql_query($query);
          echo("+");
        } else { echo("."); }
        $valid_cpm[$oid] = 1;
      }
     }
    } 
  } ## End Cisco Processors

$sql = "SELECT * FROM `cpmCPU` WHERE `device_id`  = '".$device['device_id']."'";
$query = mysql_query($sql);

while ($test_cpm = mysql_fetch_array($query)) {
  $cpm_index = $test_cmp['cpmCPU_oid'];
  if(!$valid_cpm[$cpm_index]) {
    echo("-");
    mysql_query("DELETE FROM `cpmCPU` WHERE cpmCPU_id = '" . $test_cmp['cpmCPU_id'] . "'");
  }
  unset($cpm_index);
}

unset($valid_cpm);
echo("\n");

?>
