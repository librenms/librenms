#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

if($argv[1]) { $where = "AND `device_id` = '$argv[1]'"; }

function snmp_array($oid, $device, $array, $mib = 0) {
  global $config;
  $cmd  = $config['snmpbulkwalk'] . " -O Qs -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
  if($mib) { $cmd .= "-m $mib "; }
  $cmd .= $oid;
  $data = trim(shell_exec($cmd));
  $device_id = $device['device_id'];
  foreach(explode("\n", $data) as $entry) {
    list ($this_oid, $this_value) = split("=", $entry);
    list ($this_oid, $this_index) = explode(".", $this_oid);
    $this_index = trim($this_index);
    $this_oid = trim($this_oid);
    $this_value = trim($this_value);
    if(!strstr($this_value, "No Such Instance currently exists at this OID") && $this_index) {
      $array[$device_id][$this_index][$this_oid] = $this_value;
    }
  }
  return $array;
}


$i = 0;
$device_query = mysql_query("SELECT * FROM `devices` WHERE `ignore` = '0' AND `disabled` = '0' AND `status` = '1' $where ORDER BY device_id DESC");
while ($device = mysql_fetch_array($device_query)) {
  echo($device['hostname'] . "\n"); 
  $i++;
  $data_oids = array('ifName','ifDescr','ifAlias', 'ifAdminStatus', 'ifOperStatus', 'ifMtu', 'ifSpeed', 'ifHCSpeed', 'ifType', 'ifPhysAddress');
  $stat_oids = array('ifHCInOctets', 'ifHCOutOctets', 'ifInErrors', 'ifOutErrors', 'ifInUcastPkts', 'ifOutUcastPkts', 'ifInNUcastPkts', 'ifOutNUcastPkts');
  $cisco_oids = array('locIfHardType', 'vmVlan', 'vlanTrunkPortEncapsulationOperType');
  foreach ($data_oids as $oid) {
    $array = snmp_array($oid, $device, $array);
  }
  foreach ($stat_oids as $oid) {
    $array = snmp_array($oid, $device, $array);
  }
  foreach ($cisco_oids as $oid) {
    $array = snmp_array($oid, $device, $array, "CISCO-SMI:CISCO-VLAN-MEMBERSHIP-MIB:CISCO-VTP-MIB");
  }

  print_r($array);
}

echo("$i devices polled");

?>

