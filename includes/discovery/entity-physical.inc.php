<?php

 echo("Physical Inventory : ");

 if ($config['enable_inventory']) {

  echo("\nCaching OIDs:");

  $entity_array = array();
  echo(" entPhysicalEntry");
  $entity_array = snmpwalk_cache_oid($device, "entPhysicalEntry", $entity_array, "ENTITY-MIB:CISCO-ENTITY-VENDORTYPE-OID-MIB");
  echo(" entAliasMappingIdentifier");
  $entity_array = snmpwalk_cache_twopart_oid($device, "entAliasMappingIdentifier", $entity_array, "ENTITY-MIB:IF-MIB");

  foreach ($entity_array as $entPhysicalIndex => $entry) {

    $entPhysicalDescr                = $entry['entPhysicalDescr'];
    $entPhysicalContainedIn        = $entry['entPhysicalContainedIn'];
    $entPhysicalClass                = $entry['entPhysicalClass'];
    $entPhysicalName                = $entry['entPhysicalName'];
    $entPhysicalSerialNum        = $entry['entPhysicalSerialNum'];
    $entPhysicalModelName        = $entry['entPhysicalModelName'];
    $entPhysicalMfgName                = $entry['entPhysicalMfgName'];
    $entPhysicalVendorType        = $entry['entPhysicalVendorType'];
    $entPhysicalParentRelPos        = $entry['entPhysicalParentRelPos'];
    $entPhysicalHardwareRev         = $entry['entPhysicalHardwareRev'];
    $entPhysicalFirmwareRev         = $entry['entPhysicalFirmwareRev'];
    $entPhysicalSoftwareRev         = $entry['entPhysicalSoftwareRev'];
    $entPhysicalIsFRU                 = $entry['entPhysicalIsFRU'];
    $entPhysicalAlias             = $entry['entPhysicalAlias'];
    $entPhysicalAssetID         = $entry['entPhysicalAssetID'];

    if (isset($entity_array['$entPhysicalIndex']['0']['entAliasMappingIdentifier'])) { $ifIndex = $entity_array['$entPhysicalIndex']['0']['entAliasMappingIdentifier']; }

    if (!strpos($ifIndex, "fIndex") || $ifIndex == "") { unset($ifIndex);  }
    list(,$ifIndex) = explode(".", $ifIndex);

    if ($entPhysicalVendorTypes[$entPhysicalVendorType] && !$entPhysicalModelName)
    {
      $entPhysicalModelName = $entPhysicalVendorTypes[$entPhysicalVendorType];
    }

    /// FIXME - dbFacile

    if ($entPhysicalDescr || $entPhysicalName)
    {
      $entPhysical_id = @mysql_result(mysql_query("SELECT entPhysical_id FROM `entPhysical` WHERE device_id = '".$device['device_id']."' AND entPhysicalIndex = '$entPhysicalIndex'"),0);

      if ($entPhysical_id) {
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
        $sql  = "INSERT INTO `entPhysical` (`device_id` , `entPhysicalIndex` , `entPhysicalDescr` , `entPhysicalClass` , `entPhysicalName` , `entPhysicalModelName` , `entPhysicalSerialNum` , `entPhysicalContainedIn`, `entPhysicalMfgName`, `entPhysicalParentRelPos`, `entPhysicalVendorType`, `entPhysicalHardwareRev`,`entPhysicalFirmwareRev`,`entPhysicalSoftwareRev`,`entPhysicalIsFRU`,`entPhysicalAlias`,`entPhysicalAssetID`, `ifIndex`) ";
        $sql .= "VALUES ( '" . $device['device_id'] . "', '$entPhysicalIndex', '$entPhysicalDescr', '$entPhysicalClass', '$entPhysicalName', '$entPhysicalModelName', '$entPhysicalSerialNum', '$entPhysicalContainedIn', '$entPhysicalMfgName','$entPhysicalParentRelPos' , '$entPhysicalVendorType', '$entPhysicalHardwareRev', '$entPhysicalFirmwareRev', '$entPhysicalSoftwareRev', '$entPhysicalIsFRU', '$entPhysicalAlias', '$entPhysicalAssetID', '$ifIndex')";
        mysql_query($sql);
        echo("+");
      }

      $valid[$entPhysicalIndex] = 1;
    }
  }

 } else { echo("Disabled!"); }

  $sql = "SELECT * FROM `entPhysical` WHERE `device_id`  = '".$device['device_id']."'";
  $query = mysql_query($sql);
  while ($test = mysql_fetch_assoc($query)) {
    $id = $test['entPhysicalIndex'];
    if (!$valid[$id]) {
      echo("-");
      mysql_query("DELETE FROM `entPhysical` WHERE entPhysical_id = '".$test['entPhysical_id']."'");
    }
  }

 echo("\n");

?>
