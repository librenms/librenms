<?php

$chassis_array = snmpwalk_cache_multi_oid($device, 'systeminformation', $chassis_array, 'proware-SNMP-MIB');
$id = 1;
foreach ($chassis_array as $chassis_contents) {
    // Discover the chassis
    $entity_array[] = [
        'entPhysicalIndex'        => $id++,
        'entPhysicalDescr'        => "Eurostore {$chassis_contents['siModel']}",
        'entPhysicalClass'        => 'chassis',
        'entPhysicalModelName'    => $chassis_contents['siModel'],
        'entPhysicalSerialNum'    => $chassis_contents['siSerial'],
        'entPhysicalContainedIn'  => '0',
        'entPhysicalVendorType'   => $chassis_contents['siVendor'],
        'entPhysicalHardwareRev'  => $chassis_contents['siBootVer'],
        'entPhysicalFirmwareRev'  => $chassis_contents['siFirmVer'],
    ];
}

for ($i = 1; $i <= 8; $i++) {
    $backplane_array = snmpwalk_cache_multi_oid($device, 'hwEnclosure' . $i, $backplane_array, 'proware-SNMP-MIB');

    foreach ($backplane_array as $backplane_contents) {
        if ($backplane_contents['hwEnclosure0' . $i . 'Installed'] != 2) {
            continue;
        }
        $backplane_id = $id++;
        // Discover the chassis
        $entity_array[] = [
            'entPhysicalIndex'        => $backplane_id,
            'entPhysicalDescr'        => $backplane_contents['hwEnclosure0' . $i . 'Description'],
            'entPhysicalClass'        => 'backplane',
            'entPhysicalContainedIn'  => '1',
            'entPhysicalParentRelPos' => $i,
        ];

        $hdd_array = snmpwalk_cache_multi_oid($device, 'hddEnclosure0' . $i . 'InfoTable', $hdd_array, 'proware-SNMP-MIB');
        var_dump($hdd_array);
        foreach ($hdd_array as $hdd_contents) {
            // Discover the chassis
            $entity_array[] = [
                'entPhysicalContainedIn'  => $backplane_id,
                'entPhysicalIndex'        => $id++,
                'entPhysicalDescr'        => $hdd_contents['hddEnclosure0' . $i . 'Desc'],
                'entPhysicalClass'        => 'container',
                'entPhysicalParentRelPos' => $hdd_contents['hddEnclosure0' . $i . 'Slots'],
                'entPhysicalName'         => $hdd_contents['hddEnclosure0' . $i . 'Name'],
                'entPhysicalSerialNum'    => $hdd_contents['hddEnclosure0' . $i . 'Serial'],
                'entPhysicalFirmwareRev'  => $hdd_contents['hddEnclosure0' . $i . 'FrimVer'],
                'entPhysicalIsFRU'        => 'true',
                'entPhysicalAlias'        => $hdd_contents['hddEnclosure0' . $i . 'State'],
            ];
        }
    }
}

foreach ($entity_array as $entPhysicalIndex => $entry) {
    $entPhysicalIndex = array_key_exists('entPhysicalIndex', $entry) ? $entry['entPhysicalIndex'] : '';
    $entPhysicalDescr = array_key_exists('entPhysicalDescr', $entry) ? $entry['entPhysicalDescr'] : '';
    $entPhysicalClass = array_key_exists('entPhysicalClass', $entry) ? $entry['entPhysicalClass'] : '';
    $entPhysicalName = array_key_exists('entPhysicalName', $entry) ? $entry['entPhysicalName'] : '';
    $entPhysicalModelName = array_key_exists('entPhysicalModelName', $entry) ? $entry['entPhysicalModelName'] : '';
    $entPhysicalSerialNum = array_key_exists('entPhysicalSerialNum', $entry) ? $entry['entPhysicalSerialNum'] : '';
    $entPhysicalContainedIn = array_key_exists('entPhysicalContainedIn', $entry) ? $entry['entPhysicalContainedIn'] : '';
    $entPhysicalMfgName = array_key_exists('entPhysicalMfgName', $entry) ? $entry['entPhysicalMfgName'] : '';
    $entPhysicalParentRelPos = array_key_exists('entPhysicalParentRelPos', $entry) ? $entry['entPhysicalParentRelPos'] : '';
    $entPhysicalVendorType = array_key_exists('entPhysicalVendorType', $entry) ? $entry['entPhysicalVendorType'] : '';
    $entPhysicalHardwareRev = array_key_exists('entPhysicalHardwareRev', $entry) ? $entry['entPhysicalHardwareRev'] : '';
    $entPhysicalFirmwareRev = array_key_exists('entPhysicalFirmwareRev', $entry) ? $entry['entPhysicalFirmwareRev'] : '';
    $entPhysicalSoftwareRev = array_key_exists('entPhysicalSoftwareRev', $entry) ? $entry['entPhysicalSoftwareRev'] : '';
    $entPhysicalIsFRU = array_key_exists('entPhysicalIsFRU', $entry) ? $entry['entPhysicalIsFRU'] : '';
    $entPhysicalAlias = array_key_exists('entPhysicalAlias', $entry) ? $entry['entPhysicalAlias'] : '';
    $entPhysicalAssetID = array_key_exists('entPhysicalAssetID', $entry) ? $entry['entPhysicalAssetID'] : '';
    $ifIndex = array_key_exists('ifIndex', $entry) ? $entry['ifIndex'] : '';

    discover_entity_physical($valid, $device, $entPhysicalIndex, $entPhysicalDescr, $entPhysicalClass, $entPhysicalName, $entPhysicalModelName, $entPhysicalSerialNum, $entPhysicalContainedIn, $entPhysicalMfgName, $entPhysicalParentRelPos, $entPhysicalVendorType, $entPhysicalHardwareRev, $entPhysicalFirmwareRev, $entPhysicalSoftwareRev, $entPhysicalIsFRU, $entPhysicalAlias, $entPhysicalAssetID, $ifIndex);
}//end foreach

echo "\n";
unset(
$update_data,
$insert_data,
$entry,
$entity_array
);
