<?php

if ($config['enable_inventory']) {
    echo 'Physical Inventory : ';

    echo "\nCaching OIDs:";

    if ($device['os'] == 'junos') {
        $entity_array = array();
        echo ' jnxBoxAnatomy';
        $entity_array = snmpwalk_cache_oid($device, 'jnxBoxAnatomy', $entity_array, 'JUNIPER-MIB');
    }
    else {
        $entity_array = array();
        echo ' entPhysicalEntry';
        $entity_array = snmpwalk_cache_oid($device, 'entPhysicalEntry', $entity_array, 'ENTITY-MIB:CISCO-ENTITY-VENDORTYPE-OID-MIB');
        echo ' entAliasMappingIdentifier';
        $entity_array = snmpwalk_cache_twopart_oid($device, 'entAliasMappingIdentifier', $entity_array, 'ENTITY-MIB:IF-MIB');
    }

    foreach ($entity_array as $entPhysicalIndex => $entry) {
        if ($device['os'] == 'junos') {
            // Juniper's MIB doesn't have the same objects as the Entity MIB, so some values
            // are made up here.
            $entPhysicalDescr        = $entry['jnxContentsDescr'];
            $entPhysicalContainedIn  = $entry['jnxContainersWithin'];
            $entPhysicalClass        = $entry['jnxBoxClass'];
            $entPhysicalName         = $entry['jnxOperatingDescr'];
            $entPhysicalSerialNum    = $entry['jnxContentsSerialNo'];
            $entPhysicalModelName    = $entry['jnxContentsPartNo'];
            $entPhysicalMfgName      = 'Juniper';
            $entPhysicalVendorType   = 'Juniper';
            $entPhysicalParentRelPos = -1;
            $entPhysicalHardwareRev  = $entry['jnxContentsRevision'];
            $entPhysicalFirmwareRev  = $entry['entPhysicalFirmwareRev'];
            $entPhysicalSoftwareRev  = $entry['entPhysicalSoftwareRev'];
            $entPhysicalIsFRU        = $entry['jnxFruType'];
            $entPhysicalAlias        = $entry['entPhysicalAlias'];
            $entPhysicalAssetID      = $entry['entPhysicalAssetID'];
            // fix for issue 1865, $entPhysicalIndex, as it contains a quad dotted number on newer Junipers
            // using str_replace to remove all dots should fix this even if it changes in future
	    $entPhysicalIndex = str_replace('.','',$entPhysicalIndex);
        }
        else {
            $entPhysicalDescr        = $entry['entPhysicalDescr'];
            $entPhysicalContainedIn  = $entry['entPhysicalContainedIn'];
            $entPhysicalClass        = $entry['entPhysicalClass'];
            $entPhysicalName         = $entry['entPhysicalName'];
            $entPhysicalSerialNum    = $entry['entPhysicalSerialNum'];
            $entPhysicalModelName    = $entry['entPhysicalModelName'];
            $entPhysicalMfgName      = $entry['entPhysicalMfgName'];
            $entPhysicalVendorType   = $entry['entPhysicalVendorType'];
            $entPhysicalParentRelPos = $entry['entPhysicalParentRelPos'];
            $entPhysicalHardwareRev  = $entry['entPhysicalHardwareRev'];
            $entPhysicalFirmwareRev  = $entry['entPhysicalFirmwareRev'];
            $entPhysicalSoftwareRev  = $entry['entPhysicalSoftwareRev'];
            $entPhysicalIsFRU        = $entry['entPhysicalIsFRU'];
            $entPhysicalAlias        = $entry['entPhysicalAlias'];
            $entPhysicalAssetID      = $entry['entPhysicalAssetID'];
        }//end if

        if (isset($entity_array[$entPhysicalIndex]['0']['entAliasMappingIdentifier'])) {
            $ifIndex = $entity_array[$entPhysicalIndex]['0']['entAliasMappingIdentifier'];
        }

        if (!strpos($ifIndex, 'fIndex') || $ifIndex == '') {
            unset($ifIndex);
        }
        else {
            $ifIndex_array = explode('.', $ifIndex);
            $ifIndex       = $ifIndex_array[1];
        }

        if ($entPhysicalVendorTypes[$entPhysicalVendorType] && !$entPhysicalModelName) {
            $entPhysicalModelName = $entPhysicalVendorTypes[$entPhysicalVendorType];
        }

        // FIXME - dbFacile
        if ($entPhysicalDescr || $entPhysicalName) {
            $entPhysical_id = dbFetchCell('SELECT entPhysical_id FROM `entPhysical` WHERE device_id = ? AND entPhysicalIndex = ?', array($device['device_id'], $entPhysicalIndex));

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
                    'entPhysicalAssetID'      => $entPhysicalAssetID,
                );
                dbUpdate($update_data, 'entPhysical', 'device_id=? AND entPhysicalIndex=?', array($device['device_id'], $entPhysicalIndex));
                echo '.';
            }
            else {
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
                    'entPhysicalAssetID'      => $entPhysicalAssetID,
                );

                if (!empty($ifIndex)) {
                    $insert_data['ifIndex'] = $ifIndex;
                }

                dbInsert($insert_data, 'entPhysical');
                echo '+';
            }//end if

            $valid[$entPhysicalIndex] = 1;
        }//end if
    }//end foreach
}
else {
    echo 'Disabled!';
}//end if

$sql = "SELECT * FROM `entPhysical` WHERE `device_id`  = '".$device['device_id']."'";
foreach (dbFetchRows($sql) as $test) {
    $id = $test['entPhysicalIndex'];
    if (!$valid[$id]) {
        echo '-';
        dbDelete('entPhysical', 'entPhysical_id = ?', array($test['entPhysical_id']));
    }
}

echo "\n";
