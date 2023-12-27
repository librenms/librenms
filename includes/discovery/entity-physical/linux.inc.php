<?php

$controller_array = snmpwalk_cache_multi_oid($device, 'adapterInfoTable', [], 'LSI-MegaRAID-SAS-MIB');
$enclosures = snmpwalk_cache_multi_oid($device, 'enclosureTable', [], 'LSI-MegaRAID-SAS-MIB');
$drives = snmpwalk_cache_multi_oid($device, 'physicalDriveTable', [], 'LSI-MegaRAID-SAS-MIB');
$bbus = snmpwalk_cache_multi_oid($device, 'bbuTable', [], 'LSI-MegaRAID-SAS-MIB');
$entity_array = [];

foreach ($controller_array as $controller) {
    // Discover the chassis
    $entity_array[] = [
        'entPhysicalIndex' => 200 + $controller['adapterID-AIT'],
        'entPhysicalParentRelPos' => $controller['adapterID-AIT'],
        'entPhysicalDescr' => '/C' . $controller['adapterID-AIT'],
        'entPhysicalClass' => 'port',
        'entPhysicalModelName' => $controller['productName'],
        'entPhysicalSerialNum' => $controller['serialNo'],
        'entPhysicalContainedIn' => '0',
        'entPhysicalVendorType' => $controller['adapterVendorID'],
        'entPhysicalFirmwareRev' => $controller['firmwareVersion'],
    ];
}

foreach ($bbus as $bbu) {
    // Discover the chassis
    $entity_array[] = [
        'entPhysicalIndex' => 1000 + $bbu['pdIndex'],
        'entPhysicalClass' => 'charge',
        'entPhysicalModelName' => $bbu['deviceName'],
        'entPhysicalSerialNum' => $bbu['serialNumber'],
        'entPhysicalContainedIn' => 200 + $bbu['adpID'],
        'entPhysicalIsFRU' => 'true',
        'entPhysicalFirmwareRev' => $bbu['firmwareStatus'],
    ];
}

foreach ($enclosures as $enclosure) {
    // Discover the chassis
    $entity_array[] = [
        'entPhysicalIndex' => 210 + $enclosure['deviceId'],
        'entPhysicalMfgName' => $enclosure['slotCount'],
        'entPhysicalParentRelPos' => $enclosure['deviceId'],
        'entPhysicalDescr' => '/C' . $enclosure['adapterID-CDIT'] . '/E' . $enclosure['deviceId'],
        'entPhysicalClass' => 'chassis',
        'entPhysicalModelName' => $enclosure['productID'],
        'entPhysicalSerialNum' => $enclosure['enclSerialNumber'],
        'entPhysicalContainedIn' => 200 + $enclosure['adapterID-CDIT'],
        'entPhysicalVendorType' => $enclosure['adapterVendorID'],
        'entPhysicalFirmwareRev' => $enclosure['firmwareVersion'],
    ];
}

foreach ($drives as $drive) {
    // Discover the chassis
    $entity_array[] = [
        'entPhysicalIndex' => 500 + $drive['enclDeviceId'] * 100 + $drive['physDevID'],
        'entPhysicalParentRelPos' => $drive['slotNumber'],
        'entPhysicalDescr' => '/C' . $drive['adpID-PDT'] . '/E' . $drive['enclDeviceId'] . '/S' . $drive['slotNumber'],
        'entPhysicalClass' => 'drive',
        'entPhysicalModelName' => $drive['pdProductID'],
        'entPhysicalSerialNum' => $drive['pdSerialNumber'],
        'entPhysicalContainedIn' => 210 + $drive['enclDeviceId'],
        'entPhysicalIsFRU' => 'true',
        'entPhysicalFirmwareRev' => $drive['pdFwversion'],
    ];
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

    discover_entity_physical($valid,
        $device,
        $entPhysicalIndex,
        $entPhysicalDescr,
        $entPhysicalClass,
        $entPhysicalName,
        $entPhysicalModelName,
        $entPhysicalSerialNum,
        $entPhysicalContainedIn,
        $entPhysicalMfgName,
        $entPhysicalParentRelPos,
        $entPhysicalVendorType,
        $entPhysicalHardwareRev,
        $entPhysicalFirmwareRev,
        $entPhysicalSoftwareRev,
        $entPhysicalIsFRU,
        $entPhysicalAlias,
        $entPhysicalAssetID,
        $ifIndex);
}//end foreach

echo "\n";
unset(
    $update_data,
    $insert_data,
    $entry,
    $entity_array
);
