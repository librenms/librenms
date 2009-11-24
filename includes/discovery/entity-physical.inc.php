<?php

 echo("Physical Inventory : ");

 unset($valid);

 if($config['enable_inventory']) {

  $empty = array();
  $entity_array = snmpwalk_cache_oid("entityPhysical", $device, $empty, "ENTITY-MIB");
  $entity_array = snmpwalk_cache_oid("entSensorValues", $device, $entity_array, "CISCO-ENTITY-SENSOR-MIB");

  foreach($entity_array[$device[device_id]] as $entPhysicalIndex => $entry) {

    $entPhysicalDescr		= $entry['entPhysicalDescr'];
    $entPhysicalContainedIn	= $entry['entPhysicalContainedIn'];
    $entPhysicalClass		= $entry['entPhysicalClass'];
    $entPhysicalName		= $entry['entPhysicalName'];
    $entPhysicalSerialNum	= $entry['entPhysicalSerialNum'];
    $entPhysicalModelName	= $entry['entPhysicalModelName'];
    $entPhysicalMfgName		= $entry['entPhysicalMfgName'];
    $entPhysicalVendorType	= $entry['entPhysicalVendorType'];
    $entPhysicalParentRelPos	= $entry['entPhysicalParentRelPos'];

    $ent_data  = $config['snmpget'] . " -m ENTITY-MIB:IF-MIB -Ovqs -";
    $ent_data  .= $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] .":".$device['port'];
    $ent_data .= " entAliasMappingIdentifier." . $entPhysicalIndex. ".0";

    $ifIndex = shell_exec($ent_data);

    if(!strpos($ifIndex, "fIndex") || $ifIndex == "") { unset($ifIndex);  }
    list(,$ifIndex) = explode(".", $ifIndex);

    if ($entPhysicalVendorTypes[$entPhysicalVendorType] && !$entPhysicalModelName) {
      $entPhysicalModelName = $entPhysicalVendorTypes[$entPhysicalVendorType];
    } 

    $entPhysical_id = @mysql_result(mysql_query("SELECT entPhysical_id FROM `entPhysical` WHERE device_id = '".$device['device_id']."' AND entPhysicalIndex = '$entPhysicalIndex'"),0);

    if($entPhysical_id) {
      $sql =  "UPDATE `entPhysical` SET `ifIndex` = '$ifIndex'";
      $sql .= ", entPhysicalIndex = '$entPhysicalIndex', entPhysicalDescr = '$entPhysicalDescr', entPhysicalClass = '$entPhysicalClass', entPhysicalName = '$entPhysicalName'";
      $sql .= ", entPhysicalModelName = '$entPhysicalModelName', entPhysicalSerialNum = '$entPhysicalSerialNum', entPhysicalContainedIn = '$entPhysicalContainedIn'";
      $sql .= ", entPhysicalMfgName = '$entPhysicalMfgName', entPhysicalParentRelPos = '$entPhysicalParentRelPos', entPhysicalVendorType = '$entPhysicalVendorType'";
      $sql .= " WHERE device_id = '".$device['device_id']."' AND entPhysicalIndex = '$entPhysicalIndex'";
      
      mysql_query($sql);
      echo(".");
    } else {
      $sql  = "INSERT INTO `entPhysical` ( `device_id` , `entPhysicalIndex` , `entPhysicalDescr` , `entPhysicalClass` , `entPhysicalName` , `entPhysicalModelName` , `entPhysicalSerialNum` , `entPhysicalContainedIn`, `entPhysicalMfgName`, `entPhysicalParentRelPos`, `entPhysicalVendorType`, `ifIndex` ) ";
      $sql .= "VALUES ( '" . $device['device_id'] . "', '$entPhysicalIndex', '$entPhysicalDescr', '$entPhysicalClass', '$entPhysicalName', '$entPhysicalModelName', '$entPhysicalSerialNum', '$entPhysicalContainedIn', '$entPhysicalMfgName','$entPhysicalParentRelPos' , '$entPhysicalVendorType', '$ifIndex')";      
      mysql_query($sql);
      echo("+");
    }

    if($entPhysicalClass == "sensor") {

#      $sensor_cmd  = $config['snmpget'] . " -m CISCO-ENTITY-SENSOR-MIB -O Uqnv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'];
#      $sensor_cmd .= " entSensorType.$entPhysicalIndex entSensorScale.$entPhysicalIndex entSensorPrecision.$entPhysicalIndex";
#      $sensor_cmd .= " entSensorValueUpdateRate.$entPhysicalIndex entSensorMeasuredEntity.$entPhysicalIndex";
#      $sensor_data = shell_exec($sensor_cmd);
#      list($entSensorType,$entSensorScale,$entSensorPrecision,$entSensorValueUpdateRate,$entSensorMeasuredEntity) = explode("\n", $sensor_data);

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
#      echo("$sql\n");
      mysql_query($sql);
    }
    $valid[$entPhysicalIndex] = 1;
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
