<?php

 echo("Physical Inventory : ");

 unset($valid);

 if($config['enable_inventory']) {

  $empty = array();
  $entity_array = snmpwalk_cache_oid("entPhysicalEntry", $device, $empty, "ENTITY-MIB");
  $entity_array = snmpwalk_cache_oid("entSensorValues", $device, $entity_array, "CISCO-ENTITY-SENSOR-MIB");

  if(!$entity_array[$device['device_id']]) { $entity_array[$device['device_id']] = array(); }

  foreach($entity_array[$device['device_id']] as $entPhysicalIndex => $entry) {

    $entPhysicalDescr		= $entry['entPhysicalDescr'];
    $entPhysicalContainedIn	= $entry['entPhysicalContainedIn'];
    $entPhysicalClass		= $entry['entPhysicalClass'];
    $entPhysicalName		= $entry['entPhysicalName'];
    $entPhysicalSerialNum	= $entry['entPhysicalSerialNum'];
    $entPhysicalModelName	= $entry['entPhysicalModelName'];
    $entPhysicalMfgName		= $entry['entPhysicalMfgName'];
    $entPhysicalVendorType	= $entry['entPhysicalVendorType'];
    $entPhysicalParentRelPos	= $entry['entPhysicalParentRelPos'];
    $entPhysicalHardwareRev 	= $entry['entPhysicalHardwareRev']; 
    $entPhysicalFirmwareRev 	= $entry['entPhysicalFirmwareRev'];
    $entPhysicalSoftwareRev 	= $entry['entPhysicalSoftwareRev'];
    $entPhysicalIsFRU 		= $entry['entPhysicalIsFRU'];
    $entPhysicalAlias     	= $entry['entPhysicalAlias'];
    $entPhysicalAssetID         = $entry['entPhysicalAssetID'];


    $ent_data  = $config['snmpget'] . " -m ENTITY-MIB:IF-MIB -Ovqs -";
    $ent_data  .= $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] .":".$device['port'];
    $ent_data .= " entAliasMappingIdentifier." . $entPhysicalIndex. ".0";

    $ifIndex = shell_exec($ent_data);

    if(!strpos($ifIndex, "fIndex") || $ifIndex == "") { unset($ifIndex);  }
    list(,$ifIndex) = explode(".", $ifIndex);

    if ($entPhysicalVendorTypes[$entPhysicalVendorType] && !$entPhysicalModelName)
    {
      $entPhysicalModelName = $entPhysicalVendorTypes[$entPhysicalVendorType];
    } 

    if ($entPhysicalDescr || $entPhysicalName)
    {
      $entPhysical_id = @mysql_result(mysql_query("SELECT entPhysical_id FROM `entPhysical` WHERE device_id = '".$device['device_id']."' AND entPhysicalIndex = '$entPhysicalIndex'"),0);

      if($entPhysical_id) {
        $sql =  "UPDATE `entPhysical` SET `ifIndex` = '$ifIndex'";
        $sql .= ", entPhysicalIndex = '$entPhysicalIndex', entPhysicalDescr = '$entPhysicalDescr', entPhysicalClass = '$entPhysicalClass', entPhysicalName = '$entPhysicalName'";
        $sql .= ", entPhysicalModelName = '$entPhysicalModelName', entPhysicalSerialNum = '$entPhysicalSerialNum', entPhysicalContainedIn = '$entPhysicalContainedIn'";
        $sql .= ", entPhysicalMfgName = '$entPhysicalMfgName', entPhysicalParentRelPos = '$entPhysicalParentRelPos', entPhysicalVendorType = '$entPhysicalVendorType'";
        $sql .= ", entPhysicalHardwareRev = '$entPhysicalHardwareRev', entPhysicalFirmwareRev = '$entPhysicalFirmwareRev', entPhysicalSoftwareRev = '$entPhysicalSoftwareRev'";
        $sql .= ", entPhysicalIsFRU = '$entPhysicalIsFRU', entPhysicalAlias = '$entPhysicalAlias', entPhysicalAssetID = '$entPhysicalAssetID'";
        $sql .= " WHERE device_id = '".$device['device_id']."' AND entPhysicalIndex = '$entPhysicalIndex'";
      
        mysql_query($sql);
        echo(".");
      } else {
        $sql  = "INSERT INTO `entPhysical` ( `device_id` , `entPhysicalIndex` , `entPhysicalDescr` , `entPhysicalClass` , `entPhysicalName` , `entPhysicalModelName` , `entPhysicalSerialNum` , `entPhysicalContainedIn`, `entPhysicalMfgName`, `entPhysicalParentRelPos`, `entPhysicalVendorType`, `entPhysicalHardwareRev`,`entPhysicalFirmwareRev`,`entPhysicalSoftwareRev`,`entPhysicalIsFRU`,`entPhysicalAlias`,`entPhysicalAssetID`, `ifIndex` ) ";
        $sql .= "VALUES ( '" . $device['device_id'] . "', '$entPhysicalIndex', '$entPhysicalDescr', '$entPhysicalClass', '$entPhysicalName', '$entPhysicalModelName', '$entPhysicalSerialNum', '$entPhysicalContainedIn', '$entPhysicalMfgName','$entPhysicalParentRelPos' , '$entPhysicalVendorType', '$entPhysicalHardwareRev', '$entPhysicalFirmwareRev', '$entPhysicalSoftwareRev', '$entPhysicalIsFRU', '$entPhysicalAlias', '$entPhysicalAssetID', '$ifIndex')";      
        mysql_query($sql);
        echo("+");
      }

      if($entPhysicalClass == "sensor")
      {
        $entSensorType            = $entry['entSensorType'];
        $entSensorScale           = $entry['entSensorScale'];
        $entSensorPrecision       = $entry['entSensorPrecision'];
        $entSensorValueUpdateRate = $entry['entSensorValueUpdateRate'];
        $entSensorMeasuredEntity  = $entry['entSensorMeasuredEntity'];
	
       if($config['allow_entity_sensor'][$entSensorType]) {
          $sql =  "UPDATE `entPhysical` SET entSensorType = '$entSensorType', entSensorScale = '$entSensorScale', entSensorPrecision = '$entSensorPrecision', ";
          $sql .= " entSensorMeasuredEntity = '$entSensorMeasuredEntity'";
          $sql .= " WHERE device_id = '".$device['device_id']."' AND entPhysicalIndex = '$entPhysicalIndex'";
        } else {
          echo("!");
          $sql =  "UPDATE `entPhysical` SET entSensorType = '', entSensorScale = '', entSensorPrecision = '', entSensorMeasuredEntity = ''";
          $sql .= " WHERE device_id = '".$device['device_id']."' AND entPhysicalIndex = '$entPhysicalIndex'";
        }
        mysql_query($sql);
      }
      $valid[$entPhysicalIndex] = 1;
    }
  }

 } else { echo("Disabled!"); }

  $sql = "SELECT * FROM `entPhysical` WHERE `device_id`  = '".$device['device_id']."'";
  $query = mysql_query($sql);
  while ($test = mysql_fetch_array($query)) {
    $id = $test['entPhysicalIndex'];
    if(!$valid[$id]) {
      echo("-");
      mysql_query("DELETE FROM `entPhysical` WHERE entPhysical_id = '".$test['entPhysical_id']."'");
    }
  }

 unset($valid);

 echo("\n");

?>
