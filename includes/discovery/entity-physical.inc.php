<?php

if ($config['enable_inventory']) {
    echo "\nCaching OIDs:";

    if ($device['os'] == 'junos') {
        $entity_array = array();
        echo ' jnxBoxAnatomy';
        $entity_array = snmpwalk_cache_oid($device, 'jnxBoxAnatomy', $entity_array, 'JUNIPER-MIB');
    } elseif ($device['os'] == 'timos') {
        $entity_array = array();
        echo 'tmnxHwObjs';
        $entity_array = snmpwalk_cache_multi_oid($device, 'tmnxHwObjs', $entity_array, 'TIMETRA-CHASSIS-MIB', '+'.$config['mib_dir'].'/aos:'.$config['mib_dir']);
    } else {
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
            $entPhysicalIndex = str_replace('.', '', $entPhysicalIndex);
        } elseif ($device['os'] == 'timos') {
            $entPhysicalDescr        = $entry['tmnxCardTypeDescription'];
            $entPhysicalContainedIn  = $entry['tmnxHwContainedIn'];
            $entPhysicalClass        = $entry['tmnxHwClass'];
            $entPhysicalName         = $entry['tmnxCardTypeName'];
            $entPhysicalSerialNum    = $entry['tmnxHwSerialNumber'];
            $entPhysicalModelName    = $entry['tmnxHwMfgBoardNumber'];
            $entPhysicalMfgName      = $entry['tmnxHwMfgBoardNumber'];
            $entPhysicalVendorType   = $entry['tmnxCardTypeName'];
            $entPhysicalParentRelPos = $entry['tmnxHwParentRelPos'];
            $entPhysicalHardwareRev  = '1.0';
            $entPhysicalFirmwareRev  = $entry['tmnxHwBootCodeVersion'];
            $entPhysicalSoftwareRev  = $entry['tmnxHwBootCodeVersion'];
            $entPhysicalIsFRU        = $entry['tmnxHwIsFRU'];
            $entPhysicalAlias        = $entry['tmnxHwAlias'];
            $entPhysicalAssetID      = $entry['tmnxHwAssetID'];
            $entPhysicalIndex = str_replace('.', '', $entPhysicalIndex);
        } else {
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
        } else {
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
} else {
    echo 'Disabled!';
}//end if

// Cisco CIMC
if ($device['os'] == 'cimc') {
    $module = 'Cisco-CIMC';
    $component = new LibreNMS\Component();
    $components = $component->getComponents($device['device_id'], array('type'=>$module));

    // We only care about our device id.
    $components = $components[$device['device_id']];

    // Begin our master array, all other values will be processed into this array.
    $tblCIMC = array();

    // Let's gather some data..
    $tblUCSObjects = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.719.1', 2);

    /*
     * False == no object found - this is not an error, there is no QOS configured
     * null  == timeout or something else that caused an error, the OID's may be present but we couldn't get them.
     */
    if (is_null($tblUCSObjects)) {
        // We have to error here or we will end up deleting all our components.
        echo "Error\n";
    } else {
        // No Error, lets process things.
        d_echo("CIMC Hardware Found:\n");

        // Make sure we have an array before we try to iterate over it
        if (is_array($tblUCSObjects)) {
            // Gather entPhysical data
            $entmax = 0;
            $entphysical = array();
            $dbentphysical = $entries = dbFetchRows('SELECT * FROM entPhysical WHERE device_id=?', array($device['device_id']));
            foreach ($dbentphysical as $array) {
                $entphysical[$array['entPhysicalVendorType']] = $array;
                if ($array['entPhysicalIndex'] > $entmax) {
                    $entmax = $array['entPhysicalIndex'];
                }
            }

            // Let's extract any active faults, we will use them later.
            $faults = array();
            foreach ($tblUCSObjects['1.3.6.1.4.1.9.9.719.1.1.1.1'][5] as $fid => $fobj) {
                $fobj = preg_replace('/^\/?sys\//', '', $fobj);
                $faults[$fobj] = $tblUCSObjects['1.3.6.1.4.1.9.9.719.1.1.1.1'][3][$fid] ." - ". $tblUCSObjects['1.3.6.1.4.1.9.9.719.1.1.1.1'][11][$fid];
            }

            // Unset the faults and stats array so it isn't reported as an error later.
            unset(
                $tblUCSObjects['1.3.6.1.4.1.9.9.719.1.1.1.1'],
                $tblUCSObjects['1.3.6.1.4.1.9.9.719.1.9.14.1'],
                $tblUCSObjects['1.3.6.1.4.1.9.9.719.1.9.44.1'],
                $tblUCSObjects['1.3.6.1.4.1.9.9.719.1.30.12.1'],
                $tblUCSObjects['1.3.6.1.4.1.9.9.719.1.41.2.1'],
                $tblUCSObjects['1.3.6.1.4.1.9.9.719.1.45.36.1']
            );

            foreach ($tblUCSObjects as $tbl => $array) {
                // Remove the leading /sys/
                foreach ($array[2] as &$label) {
                    $label = preg_replace('/^\/?sys\//', '', $label);
                }

                // Lets Set some defaults.
                $entPhysicalData = array(
                    'entPhysicalHardwareRev'    => '',
                    'entPhysicalFirmwareRev'    => '',
                    'entPhysicalSoftwareRev'    => '',
                    'entPhysicalIsFRU'          => 'FALSE',
                );

                switch ($tbl) {
                    // Chassis - rack-unit-1
                    case "1.3.6.1.4.1.9.9.719.1.9.35.1":
                        foreach ($array[3] as $key => $item) {
                            $result = array();
                            $result['hwtype'] = 'chassis';
                            $result['id'] = $array[27][$key];
                            $result['label'] = $array[2][$key];
                            $result['serial'] = $array[47][$key];
                            $result['string'] = $array[32][$key] ." - ". ($array[49][$key]/1024) ."G Mem, ". $array[36][$key] ." CPU, ". $array[35][$key] ." core";
                            $result['statusoid'] = '1.3.6.1.4.1.9.9.719.1.9.35.1.43.'.$key;

                            // What is the Operability, 1 is good, everything else is bad.
                            if ($array[43][$key] != 1) {
                                // Yes, report an error
                                $result['status'] = 2;
                                $result['error'] = "Error Operability Code: ".$array[43][$key]."\n";
                            } else {
                                // No, unset any errors that may exist.
                                $result['status'] = 0;
                                $result['error'] = '';
                            }

                            // See if there are any errors on this chassis.
                            foreach ($faults as $id => $value) {
                                if (strpos($id, $result['label']) !== false) {
                                    // The fault is on this chassis.
                                    $result['status'] = 2;
                                    $result['error'] .= $value."\n";
                                }
                            }

                            // Add the ent Physical entry
                            $entPhysicalData['entPhysicalClass'] = 'chassis';
                            $entPhysicalData['entPhysicalModelName'] = $array[32][$key];
                            $entPhysicalData['entPhysicalName'] = 'Chassis';
                            $entPhysicalData['entPhysicalDescr'] = $result['string'];
                            $entPhysicalData['entPhysicalSerialNum'] = $array[47][$key];
                            list($result['entPhysical'],$entPhysicalData['entPhysicalIndex']) = setCIMCentPhysical($result['label'], $entPhysicalData, $entphysical, $entmax);
                            $valid[$entPhysicalData['entPhysicalIndex']] = 1;

                            // Add the result to the array.
                            d_echo("Chassis (".$tbl."): ".print_r($result, true)."\n");
                            $tblCIMC[] = $result;
                        }
                        break;

                    // System Board - rack-unit-1/board
                    case "1.3.6.1.4.1.9.9.719.1.9.6.1":
                        foreach ($array[3] as $key => $item) {
                            $result = array();
                            $result['hwtype'] = 'board';
                            $result['id'] = $array[5][$key];
                            $result['label'] = $array[2][$key];
                            $result['serial'] = $array[14][$key];
                            $result['string'] = $array[6][$key];
                            $result['statusoid'] = '1.3.6.1.4.1.9.9.719.1.9.6.1.9.'.$key;

                            // What is the Operability, 1 is good, everything else is bad.
                            if ($array[9][$key] != 1) {
                                // Yes, report an error
                                $result['status'] = 2;
                                $result['error'] = "Error Operability Code: ".$array[9][$key];
                            } else {
                                // No, unset any errors that may exist.
                                $result['status'] = 0;
                                $result['error'] = '';
                            }

                            // Add the ent Physical entry
                            $entPhysicalData['entPhysicalClass'] = 'backplane';
                            $entPhysicalData['entPhysicalName'] = 'System Board';
                            $entPhysicalData['entPhysicalDescr'] = $result['string'];
                            $entPhysicalData['entPhysicalSerialNum'] = $array[14][$key];
                            list($result['entPhysical'],$entPhysicalData['entPhysicalIndex']) = setCIMCentPhysical($result['label'], $entPhysicalData, $entphysical, $entmax);
                            $valid[$entPhysicalData['entPhysicalIndex']] = 1;

                            // Add the result to the array.
                            d_echo("System Board (".$tbl."): ".print_r($result, true)."\n");
                            $tblCIMC[] = $result;
                        }
                        break;

                    // Memory Modules - rack-unit-1/board/memarray-1/mem-0
                    case "1.3.6.1.4.1.9.9.719.1.30.11.1":
                        foreach ($array[3] as $key => $item) {
                            $result = array();
                            // If there is no memory module present, continue.
                            if ($array[17][$key] != 10) {
                                continue;
                            }

                            $result['hwtype'] = 'memory';
                            $result['id'] = substr($array[3][$key], 4);
                            $result['label'] = $array[2][$key];
                            $result['serial'] = $array[19][$key];
                            $result['string'] = $array[11][$key] ." - ". ($array[6][$key]/1024) ."G, ". $array[27][$key] ." Bit, ". $array[7][$key] ." Mhz, ". $array[21][$key] ." MT/s";
                            $result['statusoid'] = '1.3.6.1.4.1.9.9.719.1.30.11.1.14.'.$key;

                            // What is the Operability, 1 is good, everything else is bad.
                            if ($array[14][$key] != 1) {
                                // Yes, report an error
                                $result['status'] = 2;
                                $result['error'] = "Error Operability Code: ".$array[14][$key];
                            } else {
                                // No, unset any errors that may exist.
                                $result['status'] = 0;
                                $result['error'] = '';
                            }

                            // Add the ent Physical entry
                            $entPhysicalData['entPhysicalClass'] = 'module';
                            $entPhysicalData['entPhysicalModelName'] = $array[11][$key];
                            $entPhysicalData['entPhysicalName'] = 'Memory';
                            $entPhysicalData['entPhysicalDescr'] = $result['string'];
                            $entPhysicalData['entPhysicalSerialNum'] = $array[19][$key];
                            list($result['entPhysical'],$entPhysicalData['entPhysicalIndex']) = setCIMCentPhysical($result['label'], $entPhysicalData, $entphysical, $entmax);
                            $valid[$entPhysicalData['entPhysicalIndex']] = 1;

                            // Add the result to the array.
                            d_echo("Memory (".$tbl."): ".print_r($result, true)."\n");
                            $tblCIMC[] = $result;
                        }
                        break;

                    // CPU's - rack-unit-1/board/cpu-1
                    case "1.3.6.1.4.1.9.9.719.1.41.9.1":
                        foreach ($array[3] as $key => $item) {
                            $result = array();
                            // If there is no cpu present, continue.
                            if ($array[13][$key] != 10) {
                                continue;
                            }

                            $result['hwtype'] = 'cpu';
                            $result['id'] = substr($array[3][$key], 4);
                            $result['label'] = $array[2][$key];
                            $result['serial'] = $array[15][$key];
                            $result['string'] = $array[8][$key] ." - ". $array[5][$key] ." Cores, ". $array[20][$key] ." Threads";
                            $result['statusoid'] = '1.3.6.1.4.1.9.9.719.1.41.9.1.10.'.$key;

                            // What is the Operability, 1 is good, everything else is bad.
                            if ($array[10][$key] != 1) {
                                // Yes, report an error
                                $result['status'] = 2;
                                $result['error'] = "Error Operability Code: ".$array[10][$key];
                            } else {
                                // No, unset any errors that may exist.
                                $result['status'] = 0;
                                $result['error'] = '';
                            }

                            // Add the ent Physical entry
                            $entPhysicalData['entPhysicalClass'] = 'cpu';
                            $entPhysicalData['entPhysicalModelName'] = $array[8][$key];
                            $entPhysicalData['entPhysicalName'] = 'Processor';
                            $entPhysicalData['entPhysicalDescr'] = $result['string'];
                            $entPhysicalData['entPhysicalSerialNum'] = $array[15][$key];
                            list($result['entPhysical'],$entPhysicalData['entPhysicalIndex']) = setCIMCentPhysical($result['label'], $entPhysicalData, $entphysical, $entmax);
                            $valid[$entPhysicalData['entPhysicalIndex']] = 1;

                            // Add the result to the array.
                            d_echo("CPU (".$tbl."): ".print_r($result, true)."\n");
                            $tblCIMC[] = $result;
                        }
                        break;

                    // SAS Storage Module - rack-unit-1/board/storage-SAS-2
                    case "1.3.6.1.4.1.9.9.719.1.45.1.1":
                        foreach ($array[3] as $key => $item) {
                            $result = array();
                            $result['hwtype'] = 'sas-controller';
                            $result['id'] = substr($array[3][$key], 12);
                            $result['label'] = $array[2][$key];
                            $result['serial'] = $array[14][$key];
                            $result['string'] = $array[5][$key] ." - Rev: ". $array[13][$key] .", ". $array[9][$key] .", RAID Types: ". $array[19][$key];
                            $result['statusoid'] = '1.3.6.1.4.1.9.9.719.1.45.1.1.7.'.$key;

                            // What is the Operability, 1 is good, everything else is bad.
                            if ($array[7][$key] != 1) {
                                // Yes, report an error
                                $result['status'] = 2;
                                $result['error'] = "Error Operability Code: ".$array[7][$key];
                            } else {
                                // No, unset any errors that may exist.
                                $result['status'] = 0;
                                $result['error'] = '';
                            }

                            // Add the ent Physical entry
                            $entPhysicalData['entPhysicalClass'] = 'module';
                            $entPhysicalData['entPhysicalModelName'] = $array[5][$key];
                            $entPhysicalData['entPhysicalName'] = 'Storage Module';
                            $entPhysicalData['entPhysicalDescr'] = $result['string'];
                            $entPhysicalData['entPhysicalSerialNum'] = $array[14][$key];
                            list($result['entPhysical'],$entPhysicalData['entPhysicalIndex']) = setCIMCentPhysical($result['label'], $entPhysicalData, $entphysical, $entmax);
                            $valid[$entPhysicalData['entPhysicalIndex']] = 1;

                            // Add the result to the array.
                            d_echo("SAS Module (".$tbl."): ".print_r($result, true)."\n");
                            $tblCIMC[] = $result;
                        }
                        break;

                    // SAS Disks - rack-unit-1/board/storage-SAS-2/disk-1
                    case "1.3.6.1.4.1.9.9.719.1.45.4.1":
                        foreach ($array[3] as $key => $item) {
                            $result = array();
                            $result['hwtype'] = 'sas-disk';
                            $result['id'] = substr($array[3][$key], 5);
                            $result['label'] = $array[2][$key];
                            $result['serial'] = $array[12][$key];
                            $result['statusoid'] = '1.3.6.1.4.1.9.9.719.1.45.4.1.9.'.$key;

                            // Old Firmware returns 4294967296 as 1 MB.
                            // The if below assumes we will never have < 1 Gb on old firmware or > 4 Pb on new firmware
                            if (($array[13][$key]) > 4294967296000) {
                                // Old Firmware
                                $result['string'] = $array[14][$key] ." ". $array[7][$key] .", Rev: ". $array[11][$key] .", Size: ". round($array[13][$key]/4294967296000, 2) ." GB";
                                d_echo("Disk: ".$array[2][$key].", Raw Size: ".$array[13][$key].", converted (old FW): ".round($array[13][$key]/4294967296000, 2)."GB\n");
                            } else {
                                // New Firmware
                                $result['string'] = $array[14][$key] ." ". $array[7][$key] .", Rev: ". $array[11][$key] .", Size: ". round($array[13][$key]/1000, 2) ." GB";
                                d_echo("Disk: ".$array[2][$key].", Raw Size: ".$array[13][$key].", converted (New FW): ".round($array[13][$key]/1000, 2)."GB\n");
                            }

                            // What is the Operability, 1 is good, everything else is bad.
                            if ($array[9][$key] != 1) {
                                // Yes, report an error
                                $result['status'] = 2;
                                $result['error'] = "Error Operability Code: ".$array[9][$key];
                            } else {
                                // No, unset any errors that may exist.
                                $result['status'] = 0;
                                $result['error'] = '';
                            }

                            // Add the ent Physical entry
                            $entPhysicalData['entPhysicalClass'] = 'module';
                            $entPhysicalData['entPhysicalModelName'] = $array[14][$key];
                            $entPhysicalData['entPhysicalName'] = 'Disk';
                            $entPhysicalData['entPhysicalDescr'] = $result['string'];
                            $entPhysicalData['entPhysicalSerialNum'] = $array[12][$key];
                            list($result['entPhysical'],$entPhysicalData['entPhysicalIndex']) = setCIMCentPhysical($result['label'], $entPhysicalData, $entphysical, $entmax);
                            $valid[$entPhysicalData['entPhysicalIndex']] = 1;

                            // Add the result to the array.
                            d_echo("SAS Disk (".$tbl."): ".print_r($result, true)."\n");
                            $tblCIMC[] = $result;
                        }
                        break;

                    // LUN's - rack-unit-1/board/storage-SAS-2/lun-0
                    case "1.3.6.1.4.1.9.9.719.1.45.8.1":
                        foreach ($array[3] as $key => $item) {
                            $result = array();
                            $result['hwtype'] = 'lun';
                            $result['id'] = substr($array[3][$key], 4);
                            $result['label'] = $array[2][$key];
                            $result['serial'] = 'N/A';
                            $result['statusoid'] = '1.3.6.1.4.1.9.9.719.1.45.8.1.9.'.$key;

                            // Old Firmware returns 4294967296 as 1 MB.
                            // The if below assumes we will never have < 1 Gb on old firmware or > 4 Pb on new firmware
                            if (($array[13][$key]) > 4294967296000) {
                                // Old Firmware
                                $result['string'] = $array[3][$key] .", Size: ". round($array[13][$key]/4294967296000, 2) ." GB";
                                d_echo("LUN: ".$array[2][$key].", Raw Size: ".$array[13][$key].", converted (Old FW): ".round($array[13][$key]/4294967296000, 2)."GB\n");
                            } else {
                                // New Firmware
                                $result['string'] = $array[3][$key] .", Size: ". round($array[13][$key]/1000, 2) ." GB";
                                d_echo("LUN: ".$array[2][$key].", Raw Size: ".$array[13][$key].", converted (New FW): ".round($array[13][$key]/1000, 2)."GB\n");
                            }

                            // What is the Operability, 1 is good, everything else is bad.
                            if ($array[9][$key] != 1) {
                                // Yes, report an error
                                $result['status'] = 2;
                                $result['error'] = "Error Operability Code: ".$array[9][$key];
                            } else {
                                // No, unset any errors that may exist.
                                $result['status'] = 0;
                                $result['error'] = '';
                            }

                            // Add the ent Physical entry
                            $entPhysicalData['entPhysicalClass'] = 'module';
                            $entPhysicalData['entPhysicalModelName'] = $array[3][$key];
                            $entPhysicalData['entPhysicalName'] = 'LUN';
                            $entPhysicalData['entPhysicalDescr'] = $result['string'];
                            $entPhysicalData['entPhysicalSerialNum'] = '';
                            list($result['entPhysical'],$entPhysicalData['entPhysicalIndex']) = setCIMCentPhysical($result['label'], $entPhysicalData, $entphysical, $entmax);
                            $valid[$entPhysicalData['entPhysicalIndex']] = 1;

                            // Add the result to the array.
                            d_echo("LUN (".$tbl."): ".print_r($result, true)."\n");
                            $tblCIMC[] = $result;
                        }
                        break;

                    // RAID Battery - rack-unit-1/board/storage-SAS-2/raid-battery
                    case "1.3.6.1.4.1.9.9.719.1.45.11.1":
                        foreach ($array[3] as $key => $item) {
                            $result = array();
                            $result['hwtype'] = 'raid-battery';
                            $result['id'] = $array[3][$key];
                            $result['label'] = $array[2][$key];
                            $result['serial'] = 'N/A';
                            $result['string'] = $array[3][$key] ." - ". $array[7][$key];
                            $result['statusoid'] = '1.3.6.1.4.1.9.9.719.1.45.11.1.9.'.$key;

                            // What is the Operability, 1 is good, everything else is bad.
                            if ($array[9][$key] != 1) {
                                // Yes, report an error
                                $result['status'] = 2;
                                $result['error'] = "Error Operability Code: ".$array[9][$key];
                            } else {
                                // No, unset any errors that may exist.
                                $result['status'] = 0;
                                $result['error'] = '';
                            }

                            // Add the ent Physical entry
                            $entPhysicalData['entPhysicalClass'] = 'module';
                            $entPhysicalData['entPhysicalModelName'] = $array[3][$key];
                            $entPhysicalData['entPhysicalName'] = 'RAID Battery';
                            $entPhysicalData['entPhysicalDescr'] = $result['string'];
                            $entPhysicalData['entPhysicalSerialNum'] = '';
                            list($result['entPhysical'],$entPhysicalData['entPhysicalIndex']) = setCIMCentPhysical($result['label'], $entPhysicalData, $entphysical, $entmax);
                            $valid[$entPhysicalData['entPhysicalIndex']] = 1;

                            // Add the result to the array.
                            d_echo("RAID Battery (".$tbl."): ".print_r($result, true)."\n");
                            $tblCIMC[] = $result;
                        }
                        break;

                    // Fan's - rack-unit-1/fan-module-1-1/fan-1
                    case "1.3.6.1.4.1.9.9.719.1.15.12.1":
                        foreach ($array[3] as $key => $item) {
                            $result = array();
                            $result['hwtype'] = 'fan';
                            $result['id'] = $array[8][$key] ."-". substr($array[3][$key], 4);
                            $result['label'] = $array[2][$key];
                            $result['serial'] = 'N/A';
                            $result['string'] = $array[7][$key];
                            $result['statusoid'] = '1.3.6.1.4.1.9.9.719.1.15.12.1.10.'.$key;

                            // What is the Operability, 1 is good, everything else is bad.
                            if ($array[10][$key] != 1) {
                                // Yes, report an error
                                $result['status'] = 2;
                                $result['error'] = "Error Operability Code: ".$array[10][$key];
                            } else {
                                // No, unset any errors that may exist.
                                $result['status'] = 0;
                                $result['error'] = '';
                            }

                            // Add the ent Physical entry
                            $entPhysicalData['entPhysicalClass'] = 'fan';
                            $entPhysicalData['entPhysicalModelName'] = $array[7][$key];
                            $entPhysicalData['entPhysicalName'] = 'FAN';
                            $entPhysicalData['entPhysicalDescr'] = $result['string'];
                            $entPhysicalData['entPhysicalSerialNum'] = '';
                            list($result['entPhysical'],$entPhysicalData['entPhysicalIndex']) = setCIMCentPhysical($result['label'], $entPhysicalData, $entphysical, $entmax);
                            $valid[$entPhysicalData['entPhysicalIndex']] = 1;

                            // Add the result to the array.
                            d_echo("Fan (".$tbl."): ".print_r($result, true)."\n");
                            $tblCIMC[] = $result;
                        }
                        break;

                    // PSU's - rack-unit-1/psu-1
                    case "1.3.6.1.4.1.9.9.719.1.15.56.1":
                        foreach ($array[3] as $key => $item) {
                            $result = array();
                            $result['hwtype'] = 'psu';
                            $result['id'] = substr($array[3][$key], 4);
                            $result['label'] = $array[2][$key];
                            $result['serial'] = $array[13][$key];
                            $result['string'] = $array[6][$key] ." - Rev: ". $array[12][$key];
                            $result['statusoid'] = '1.3.6.1.4.1.9.9.719.1.15.56.1.8.'.$key;

                            // What is the Operability, 1 is good, everything else is bad.
                            if ($array[8][$key] != 1) {
                                // Yes, report an error
                                $result['status'] = 2;
                                $result['error'] = "Error Operability Code: ".$array[8][$key];
                            } else {
                                // No, unset any errors that may exist.
                                $result['status'] = 0;
                                $result['error'] = '';
                            }

                            // Add the ent Physical entry
                            $entPhysicalData['entPhysicalClass'] = 'powerSupply';
                            $entPhysicalData['entPhysicalModelName'] = $array[6][$key];
                            $entPhysicalData['entPhysicalName'] = 'PSU';
                            $entPhysicalData['entPhysicalDescr'] = $result['string'];
                            $entPhysicalData['entPhysicalSerialNum'] = $array[13][$key];
                            list($result['entPhysical'],$entPhysicalData['entPhysicalIndex']) = setCIMCentPhysical($result['label'], $entPhysicalData, $entphysical, $entmax);
                            $valid[$entPhysicalData['entPhysicalIndex']] = 1;

                            // Add the result to the array.
                            d_echo("PSU (".$tbl."): ".print_r($result, true)."\n");
                            $tblCIMC[] = $result;
                        }
                        break;

                    // Adaptors - rack-unit-1/adaptor-1
                    case "1.3.6.1.4.1.9.9.719.1.3.85.1":
                        foreach ($array[3] as $key => $item) {
                            $result = array();
                            $result['hwtype'] = 'adaptor';
                            $result['id'] = substr($array[3][$key], 8);
                            $result['label'] = $array[2][$key];
                            $result['serial'] = $array[21][$key];
                            $result['string'] = $array[11][$key] ." - Rev: ". $array[20][$key] ." - Part-No: ". $array[26][$key];
                            $result['statusoid'] = '1.3.6.1.4.1.9.9.719.1.3.85.1.13.'.$key;

                            // What is the Operability, 1 is good, everything else is bad.
                            if ($array[13][$key] != 1) {
                                // Yes, report an error
                                $result['status'] = 2;
                                $result['error'] = "Error Operability Code: ".$array[13][$key];
                            } else {
                                // No, unset any errors that may exist.
                                $result['status'] = 0;
                                $result['error'] = '';
                            }

                            // Add the ent Physical entry
                            $entPhysicalData['entPhysicalClass'] = 'module';
                            $entPhysicalData['entPhysicalModelName'] = $array[11][$key];
                            $entPhysicalData['entPhysicalName'] = 'Adaptor';
                            $entPhysicalData['entPhysicalDescr'] = $result['string'];
                            $entPhysicalData['entPhysicalSerialNum'] = $array[21][$key];
                            list($result['entPhysical'],$entPhysicalData['entPhysicalIndex']) = setCIMCentPhysical($result['label'], $entPhysicalData, $entphysical, $entmax);
                            $valid[$entPhysicalData['entPhysicalIndex']] = 1;

                            // Add the result to the array.
                            d_echo("Adaptor (".$tbl."): ".print_r($result, true)."\n");
                            $tblCIMC[] = $result;
                        }
                        break;

                    // Unknown Table, ask the user to log an issue so this can be identified.
                    default:
                        d_echo("Cisco-CIMC Error...\n");
                        d_echo("Please log an issue on github with the following information:\n");
                        d_echo("-----------------------------------------------\n");
                        d_echo("Subject: CIMC Unknown Table: ".$tbl."\n");
                        d_echo("Description: The entity-physical module discovered an unknown CIMC table.\nA dump of its contents is below:\n");
                        d_echo($array);
                        d_echo("-----------------------------------------------\n\n");
                        break;
                } // End Switch
            } // End foreach tblUCSObjects
        } // End is_array

        /*
         * Ok, we have our 2 array's (Components and SNMP) now we need
         * to compare and see what needs to be added/updated.
         *
         * Let's loop over the SNMP data to see if we need to ADD or UPDATE any components.
         */
        foreach ($tblCIMC as $key => $array) {
            $component_key = false;

            // Loop over our components to determine if the component exists, or we need to add it.
            foreach ($components as $compid => $child) {
                if ($child['label'] === $array['label']) {
                    $component_key = $compid;
                }
            }

            if (!$component_key) {
                // The component doesn't exist, we need to ADD it - ADD.
                $new_component = $component->createComponent($device['device_id'], $module);
                $component_key = key($new_component);
                $components[$component_key] = array_merge($new_component[$component_key], $array);
                echo "+";
            } else {
                // The component does exist, merge the details in - UPDATE.
                $components[$component_key] = array_merge($components[$component_key], $array);
                echo ".";
            }
        }

        /*
         * Loop over the Component data to see if we need to DELETE any components.
         */
        foreach ($components as $key => $array) {
            // Guilty until proven innocent
            $found = false;

            foreach ($tblCIMC as $k => $v) {
                if ($array['label'] == $v['label']) {
                    // Yay, we found it...
                    $found = true;
                }
            }

            if ($found === false) {
                // The component has not been found. we should delete it and it's entPhysical entry
                echo "-";
                dbDelete('entPhysical', '`entPhysical_id` = ?', array($array['entPhysical']));
                $component->deleteComponent($key);
            }
        }
        // Write the Components back to the DB.
        $component->setComponentPrefs($device['device_id'], $components);
        echo "\n";
    } // End if not error
}

$sql = "SELECT * FROM `entPhysical` WHERE `device_id`  = '".$device['device_id']."'";
foreach (dbFetchRows($sql) as $test) {
    $id = $test['entPhysicalIndex'];
    if (!$valid[$id]) {
        echo '-';
        dbDelete('entPhysical', 'entPhysical_id = ?', array($test['entPhysical_id']));
    }
}

echo "\n";
