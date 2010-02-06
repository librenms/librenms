<?php

echo("hrDevice : ");

$hrDevice_oids = array('hrDeviceIndex','hrDeviceType','hrDeviceDescr','hrDeviceStatus','hrDeviceErrors','hrProcessorLoad');


foreach ($hrDevice_oids as $oid) { $hrDevice_array = snmp_cache_oid($oid, $device, $hrDevice_array, "HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES"); }

foreach($hrDevice_array[$device['device_id']] as $hrDevice) {
  if(is_array($hrDevice)) {
   if(mysql_result(mysql_query("SELECT COUNT(*) FROM `hrDevice` WHERE device_id = '".$device['device_id']."' AND hrDeviceIndex = '".$hrDevice['hrDeviceIndex']."'"),0)) {
     $update_query  = "UPDATE `hrDevice` SET";
     $update_query .= "  `hrDeviceType` = '".mres($hrDevice[hrDeviceType])."'";
     $update_query .= ", `hrDeviceDescr` = '".mres($hrDevice[hrDeviceDescr])."'";
     $update_query .= ", `hrDeviceStatus` = '".mres($hrDevice[hrDeviceStatus])."'";
     $update_query .= ", `hrDeviceErrors` = '".mres($hrDevice[hrDeviceErrors])."'";
     if($hrDevice['hrDeviceType'] == "hrDeviceProcessor") {
       $update_query .= ", `hrProcessorLoad` = '".mres($hrDevice[hrProcessorLoad])."'";
     }
     $update_query .= " WHERE device_id = '".$device['device_id']."' AND hrDeviceIndex = '".$hrDevice['hrDeviceIndex']."'";
     @mysql_query($update_query); $mysql++; echo(".");
   } else {
     $insert_query = "INSERT INTO `hrDevice` (`hrDeviceIndex`,`device_id`,`hrDeviceType`,`hrDeviceDescr`,`hrDeviceStatus`,`hrDeviceErrors`) ";
     $insert_query .= " VALUES ('".mres($hrDevice[hrDeviceIndex])."','".mres($device[device_id])."','".mres($hrDevice[hrDeviceType])."','".mres($hrDevice[hrDeviceDescr])."','".mres($hrDevice[hrDeviceStatus])."','".mres($hrDevice[hrDeviceErrors])."')";
     @mysql_query($insert_query); $mysql++; echo("+");
   }
   $valid_hrDevice[$hrDevice[hrDeviceIndex]] = 1;
  }
}

$sql = "SELECT * FROM `hrDevice` WHERE `device_id`  = '".$device['device_id']."'";
$query = mysql_query($sql);

while ($test_hrDevice = mysql_fetch_array($query)) {
  if(!$valid_hrDevice[$test_hrDevice[hrDeviceIndex]]) {
    echo("-");
    mysql_query("DELETE FROM `hrDevice` WHERE hrDevice_id = '" . $test_hrDevice['hrDevice_id'] . "'");
  }
}

unset($valid_hrDevice);
echo("\n");

?>
