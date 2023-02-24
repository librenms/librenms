<?php

$physical_name = snmpwalk_cache_oid($device, 'sysObjectID.0', $physical_name, 'SNMPv2-MIB:CALIX-PRODUCT-MIB');
$serial_number = snmpwalk_cache_oid($device, 'axosSystemChassisSerialNumber', $serial_number, 'Axos-System-MIB');
$entity_array[] = [
    'entPhysicalIndex' => 0,
    'entPhysicalName' => $physical_name['sysObjectID.0'],
    'entPhysicalSerialNum' => $serial_number['axosSystemChassisSerialNumber.0'],
    'entPhysicalMfgName' => 'Calix',
    'entPhysicalVendorType' => 'Calix',
    'entPhysicalParentRelPos' => 0,
];

$card_array = snmpwalk_cache_multi_oid($device, 'axosCardTable', $card_array, 'Axos-Card-MIB');
$id = 1;
foreach ($card_array as $card) {
    // Discover the card
    $entity_array[] = [
        'entPhysicalIndex'        => $id++,
        'entPhysicalDescr'        => "Calix {$card['axosCardActualType']}",
        'entPhysicalClass'        => 'card',
        'entPhysicalModelName'    => $card['axosCardPartNumber'],
        'entPhysicalSerialNum'    => $card['axosCardSerialNumber'],
        'entPhysicalContainedIn'  => '0',
        'entPhysicalSoftwareRev'  => $card['axosCardSoftwareVersion'],
        'entPhysicalIsFRU'        => true,
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

    discover_entity_physical($valid, $device, $entPhysicalIndex, $entPhysicalDescr, $entPhysicalClass, $entPhysicalName, $entPhysicalModelName, $entPhysicalSerialNum, $entPhysicalContainedIn, $entPhysicalMfgName, $entPhysicalParentRelPos, $entPhysicalVendorType, $entPhysicalHardwareRev, $entPhysicalFirmwareRev, $entPhysicalSoftwareRev, $entPhysicalIsFRU, $entPhysicalAlias, $entPhysicalAssetID, $ifIndex);
}//end foreach

echo "\n";
unset(
$physical_name,
$serial_number,
$card_array,
$card,
$entry,
$entity_array,
$id
);
