#!/usr/bin/php
<?
include("config.php");
include("includes/functions.php");

$device_query = mysql_query("SELECT * FROM `devices` WHERE status = '1' AND os = 'Linux' OR os = 'FreeBSD' OR os = 'NetBSD' OR os = 'OpenBSD' OR os = 'DragonFly' ORDER BY `device_id` DESC");
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
      if(mysql_result(mysql_query("SELECT count(storage_id) FROM `storage` WHERE hrStorageIndex = '$hrStorageIndex' AND host_id = '$id'"),0) == '0') {
        $query  = "INSERT INTO storage (`host_id`, `hrStorageIndex`, `hrStorageDescr`,`hrStorageSize`,`hrStorageAllocationUnits`) ";
        $query .= "values ('$id', '$hrStorageIndex', '$descr', '$size', '$units')";
	mysql_query($query);
	echo("Adding $descr\n");
      } else {
        $data = mysql_fetch_array(mysql_query("SELECT * FROM `storage` WHERE hrStorageIndex = '$hrStorageIndex' AND host_id = '$id'"));
	if($data['hrStorageDescr'] != $descr || $data['hrStorageSize'] != $size || $data['hrStorageAllocationUnits'] != $units ) {
          $query  = "UPDATE storage SET `hrStorageDescr` = '$descr', `hrStorageSize` = '$size', `hrStorageAllocationUnits` = '$units' ";
          $query .= "WHERE hrStorageIndex = '$hrStorageIndex' AND host_id = '$id'";
	  echo("Updating $descr\n");
	  mysql_query($query);
        }
      }
      $storage_exists[] = "$id $hrStorageIndex";
    }
  }
}

$sql = "SELECT * FROM storage AS S, devices AS D where S.host_id = D.device_id AND D.status = '1'";
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
          echo("Deleting " . $store['hrStorageDescr'] . " from " . $store['hostname'] . "\n");
          mysql_query("DELETE FROM storage WHERE storage_id = '" . $store['storage_id'] . "'");
        }


}



?>
