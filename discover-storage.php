#!/usr/bin/php
<?
include("config.php");
include("includes/functions.php");

$device_query = mysql_query("SELECT * FROM `devices` WHERE os = 'Linux' OR os = 'FreeBSD' OR os = 'NetBSD' OR os = 'OpenBSD' OR os = 'DragonFly' AND monowall = '0' AND status = '1'");
while ($device = mysql_fetch_array($device_query)) {
  $id = $device['id'];
  $hostname = $device['hostname'];
  $community = $device['community'];
  echo("\n***$hostname***\n");
  $oids = `snmpwalk -v2c -Osq -c $community $hostname hrStorageIndex | sed s/hrStorageIndex.//g`;
  $oids = trim($oids);
  foreach(explode("\n", $oids) as $data) {
    $data = trim($data);
    list($oid,$ifIndex) = explode(" ", $data);
    $temp = `snmpget -O qv -v2c -c $community $hostname hrStorageDescr.$oid hrStorageAllocationUnits.$oid hrStorageSize.$oid hrStorageType.$oid`;
    $temp = trim($temp);
    list($descr, $units, $size, $type) = explode("\n", $temp);
    list($units) = explode(" ", $units);
    if(strstr($type, "FixedDisk") && $size > '0') {
      echo("$oid,$descr,$units,$size\n");
      if(mysql_result(mysql_query("SELECT count(storage_id) FROM `storage` WHERE hrStorageIndex = '$ifIndex' AND host_id = '$id'"),0) == '0') {
        $query = "INSERT INTO storage (`host_id`, `hrStorageIndex`, `hrStorageDescr`,`hrStorageSize`,`hrStorageAllocationUnits`) values ('$id', '$ifIndex', '$descr', '$size', '$units')";
        echo("$query \n");
	mysql_query($query);
      }
    }
  }
}
?>
