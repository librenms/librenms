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

    if (isset($entity_array[$entPhysicalIndex]['0']['entAliasMappingIdentifier'])) {
      $ifIndex = $entity_array[$entPhysicalIndex]['0']['entAliasMappingIdentifier'];
    }

    if (!strpos($ifIndex, "fIndex") || $ifIndex == "") {
      unset($ifIndex);
    } else {
      $ifIndex_array = explode(".", $ifIndex);
      $ifIndex = $ifIndex_array[1];
    }

    if ($entPhysicalVendorTypes[$entPhysicalVendorType] && !$entPhysicalModelName)
    {
      $entPhysicalModelName = $entPhysicalVendorTypes[$entPhysicalVendorType];
    }

    // FIXME - dbFacile

    if ($entPhysicalDescr || $entPhysicalName)
    {
      $entPhysical_id = dbFetchCell("SELECT entPhysical_id FROM `entPhysical` WHERE device_id = ? AND entPhysicalIndex = ?",array($device['device_id'], $entPhysicalIndex));

      if ($entPhysical_id) {
        $update_data = array(
          'entPhysicalIndex'        => $entPhysicalIndex,
          'entPhysicalDescr'        => $entPhysicalDescr,
          'entPhysicalClass'        => $entPhysicalClass,
          'entPhysicalName'         => $entPhysicalName,
          'entPhysicalModelName'    => $entPhysicalModelName,
          'entPhysicalSerialNum'    => $entPhysicalSerialNum,
          'entPhysicalContainedIn'  => $entPhysicalContainedIn,
          'entPhysicalMfgName'      => $entPhysicalMfgName,
          'entPhysicalParentRelPos' => $entPhysicalParentRelPos,
          'entPhysicalVendorType'   => $entPhysicalVendorType,
          'entPhysicalHardwareRev'  => $entPhysicalHardwareRev,
          'entPhysicalFirmwareRev'  => $entPhysicalFirmwareRev,
          'entPhysicalSoftwareRev'  => $entPhysicalSoftwareRev,
          'entPhysicalIsFRU'        => $entPhysicalIsFRU,
          'entPhysicalAlias'        => $entPhysicalAlias,
          'entPhysicalAssetID'      => $entPhysicalAssetID
        );
        dbUpdate($update_data, 'entPhysical', 'device_id=? AND entPhysicalIndex=?',array($device['device_id'],$entPhysicalIndex));
        echo(".");
      } else {
        $insert_data = array(
          'device_id'               => $device['device_id'],
          'entPhysicalIndex'        => $entPhysicalIndex,
          'entPhysicalDescr'        => $entPhysicalDescr,
          'entPhysicalClass'        => $entPhysicalClass,
          'entPhysicalName'         => $entPhysicalName,
          'entPhysicalModelName'    => $entPhysicalModelName,
          'entPhysicalSerialNum'    => $entPhysicalSerialNum,
          'entPhysicalContainedIn'  => $entPhysicalContainedIn,
          'entPhysicalMfgName'      => $entPhysicalMfgName,
          'entPhysicalParentRelPos' => $entPhysicalParentRelPos,
          'entPhysicalVendorType'   => $entPhysicalVendorType,
          'entPhysicalHardwareRev'  => $entPhysicalHardwareRev,
          'entPhysicalFirmwareRev'  => $entPhysicalFirmwareRev,
          'entPhysicalSoftwareRev'  => $entPhysicalSoftwareRev,
          'entPhysicalIsFRU'        => $entPhysicalIsFRU,
          'entPhysicalAlias'        => $entPhysicalAlias,
          'entPhysicalAssetID'      => $entPhysicalAssetID
        );

        if (!empty($ifIndex)) {
          $insert_data['ifIndex'] = $ifIndex;
        }

        dbInsert($insert_data, 'entPhysical');
        echo("+");
      }

      $valid[$entPhysicalIndex] = 1;
    }
  }

 } else { echo("Disabled!"); }

  $sql = "SELECT * FROM `entPhysical` WHERE `device_id`  = '".$device['device_id']."'";
  foreach (dbFetchRows($sql) as $test)
  {
    $id = $test['entPhysicalIndex'];
    if (!$valid[$id]) {
      echo("-");
      dbDelete('entPhysical', 'entPhysical_id = ?', array($test['entPhysical_id']));
    }
  }

 echo("\n");

?>
