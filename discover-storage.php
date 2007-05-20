#!/usr/bin/php
<?
include("config.php");
include("includes/functions.php");

$device_query = mysql_query("SELECT * FROM `devices` WHERE os = 'Linux' OR os = 'FreeBSD' OR os = 'NetBSD' OR os = 'OpenBSD' OR os = 'DragonFly' AND status = '1'");
while ($device = mysql_fetch_array($device_query)) {
  $id = $device['device_id'];
  $hostname = $device['hostname'];
  $community = $device['community'];
  echo("\n***$hostname***\n");
  $oids = `snmpwalk -v2c -Osq -c $community $hostname hrStorageIndex | sed s/hrStorageIndex.//g`;
  $oids = trim($oids);
  foreach(explode("\n", $oids) as $data) {
    $data = trim($data);
    list($oid,$hrStorageIndex) = explode(" ", $data);
    $temp = `snmpget -O qv -v2c -c $community $hostname hrStorageDescr.$oid hrStorageAllocationUnits.$oid hrStorageSize.$oid hrStorageType.$oid`;
    $temp = trim($temp);
    list($descr, $units, $size, $type) = explode("\n", $temp);
    list($units) = explode(" ", $units);
    if(strstr($type, "FixedDisk") && $size > '0') {
      echo("$oid,$descr,$units,$size\n");
      if(mysql_result(mysql_query("SELECT count(storage_id) FROM `storage` WHERE hrStorageIndex = '$hrStorageIndex' AND host_id = '$id'"),0) == '0') {
        $query = "INSERT INTO storage (`host_id`, `hrStorageIndex`, `hrStorageDescr`,`hrStorageSize`,`hrStorageAllocationUnits`) values ('$id', '$hrStorageIndex', '$descr', '$size', '$units')";
        echo("$query \n");
	mysql_query($query);
      }
      $storage_exists[] = "$id $hrStorageIndex";
    }
  }
}

$sql = "SELECT * FROM storage";
$query = mysql_query($sql);

while ($store = mysql_fetch_array($query)) {

        unset($exists);

        $i = 0;
        while ($i < count($storage_exists) && !$exists) {
            $thisstore = $store['host_id'] . " " . $store['hrStorageIndex'];
            if ($storage_exists[$i] == $thisstore) { $exists = 1; echo("Match!"); }
            $i++;
            echo("$storage_exists[$i] == $thisstore \n");
        }

        if(!$exists) {
          echo("Deleting...\n");
#          mysql_query("DELETE FROM storage WHERE storage_id = '" . $store['storage_id'] . "'");
        }


}



?>
