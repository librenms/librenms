#!/usr/bin/php
<?php

include("config.php");
include("includes/functions.php");

$device_query = mysql_query("SELECT * FROM `devices` WHERE status = '1' AND os = 'IOS' order by device_id DESC");
while ($device = mysql_fetch_array($device_query)) {

  echo("Discovering Physical Entities on " . $device['hostname'] . "\n");

  $ents_cmd  = "snmpwalk -O qn -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " ";
  $ents_cmd .= "1.3.6.1.2.1.47.1.1.1.1.2 | sed s/.1.3.6.1.2.1.47.1.1.1.1.2.//g | cut -f 1 -d\" \"";

  $ents  = trim(`$ents_cmd | grep -v o`);

  foreach(explode("\n", $ents) as $entPhysicalIndex) {

    $ent_data  = "snmpget -Ovqs -"; 
    $ent_data  .= $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'];
    $ent_data .= " entPhysicalDescr." . $entPhysicalIndex;
    $ent_data .= " entPhysicalContainedIn." . $entPhysicalIndex;
    $ent_data .= " entPhysicalClass." . $entPhysicalIndex;
    $ent_data .= " entPhysicalName." . $entPhysicalIndex;
    $ent_data .= " entPhysicalSerialNum." . $entPhysicalIndex;
    $ent_data .= " entPhysicalModelName." . $entPhysicalIndex;
    $ent_data .= " entPhysicalMfgName." . $entPhysicalIndex;
    $ent_data .= " entPhysicalVendorType." . $entPhysicalIndex;
    $ent_data .= " entPhysicalParentRelPos." . $entPhysicalIndex;
    $ent_data .= " entAliasMappingIdentifier." . $entPhysicalIndex. ".0";


    list($entPhysicalDescr,$entPhysicalContainedIn,$entPhysicalClass,$entPhysicalName,$entPhysicalSerialNum,$entPhysicalModelName,$entPhysicalMfgName,$entPhysicalVendorType,$entPhysicalParentRelPos, $ifIndex) = explode("\n", `$ent_data`);

    if(strpos($ifIndex, "o") || $ifIndex == "") { unset($ifIndex);  }


#    if ($entPhysicalVendorTypes['$entPhysicalVendorType']) {
#      $entPhysicalVendorType = $entPhysicalVendorTypes['$entPhysicalVendorType'];
#    } else {
#      $entPhysicalVendorType_new = `grep $entPhysicalVendorType\  /usr/share/snmp/mibs/CISCO-ENTITY-VENDORTYPE-OID-MIB.my | sed s/.*\-\-\ //| grep -v \=`;
#      $entPhysicalVendorTypes['$entPhysicalVendorType'] = $entPhysicalVendorType_new;
#      $entPhysicalVendorType = $entPhysicalVendorType_new;
#    }

    $entPhysicalModelName = trim($entPhysicalModelName);
    $entPhysicalSerialNum = trim($entPhysicalSerialNum);
    $entPhysicalMfgName = trim($entPhysicalMfgName);
    $entPhysicalVendorType = trim($entPhysicalVendorType);

    if ($entPhysicalVendorTypes[$entPhysicalVendorType] && !$entPhysicalModelName) {
      $entPhysicalModelName = $entPhysicalVendorTypes[$entPhysicalVendorType];
    } 
    #else { unset($entPhysicalVendorType); }


    echo("$entPhysicalIndex,$entPhysicalDescr,$entPhysicalContainedIn,$entPhysicalSerialNum,");
    echo("$entPhysicalClass,$entPhysicalName,$entPhysicalModelName,$entPhysicalMfgName,$entPhysicalVendorType,$entPhysicalParentRelPos\n");

    if(mysql_result(mysql_query("SELECT COUNT(*) FROM `entPhysical` WHERE device_id = '".$device['device_id']."' AND entPhysicalIndex = '$entPhysicalIndex'"),0)) {
      ## MySQL Update
#	echo("already\n");
    } else {
#       echo("insert\n");
      $sql  = "INSERT INTO `entPhysical` ( `device_id` , `entPhysicalIndex` , `entPhysicalDescr` , `entPhysicalClass` , `entPhysicalName` , `entPhysicalModelName` , `entPhysicalSerialNum` , `entPhysicalContainedIn`, `entPhysicalMfgName`, `entPhysicalParentRelPos`, `entPhysicalVendorType`, `ifIndex` ) ";
      $sql .= "VALUES ( '" . $device['device_id'] . "', '$entPhysicalIndex', '$entPhysicalDescr', '$entPhysicalClass', '$entPhysicalName', '$entPhysicalModelName', '$entPhysicalSerialNum', '$entPhysicalContainedIn', '$entPhysicalMfgName','$entPhysicalParentRelPos' , '$entPhysicalVendorType', '$ifIndex')";      
#      echo($sql . "\n");
      mysql_query($sql);
      echo(mysql_error());
    }

  }

  echo("\n");

}

?>
