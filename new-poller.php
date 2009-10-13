#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

if($argv[1]) { $where = "WHERE `device_id` = '$argv[1]'"; }

function snmp_array($oid, $device, $mib = 0) {
  global $config;
  $cmd  = $config['snmpbulkwalk'] . " -O Qs -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " ";
  if($mib) { $cmd .= "-m $mib "; }
  $cmd .= $oid;
  $data = trim(shell_exec($cmd));
  $array = array();
  $device_id = $device['device_id'];
  foreach(explode("\n", $data) as $entry) {
    list ($this_oid, $this_value) = split(" = ", $entry);
    list ($this_oid, $this_index) = explode(".", $this_oid);
    $array[$device_id][$this_oid][$this_index] = $this_value;
  }
  return $array;
}


$interface_data = array();
$device_query = mysql_query("SELECT * FROM `devices` $where ORDER BY device_id DESC");
while ($device = mysql_fetch_array($device_query)) {

  $oids = array('ifName','ifDescr','ifAlias');
  foreach ($oids as $oid) {
    $entries = snmp_array($oid, $device);
    $interface_data = @array_merge($entries, $interface_data);
  }
  print_r($interface_data);
}

?>

