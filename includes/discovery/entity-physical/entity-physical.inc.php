<?php

echo "\nCaching OIDs:";


$entity_array = [];
echo ' entPhysicalEntry';
$entity_array = snmpwalk_cache_oid($device, 'entPhysicalEntry', $entity_array, 'ENTITY-MIB:CISCO-ENTITY-VENDORTYPE-OID-MIB');

if (! empty($entity_array)) {
    echo ' entAliasMappingIdentifier';
    $entity_array = snmpwalk_cache_twopart_oid($device, 'entAliasMappingIdentifier', $entity_array, 'ENTITY-MIB:IF-MIB');
}

foreach ($entity_array as $entPhysicalIndex => $entry) {
    $ifIndex = 0;
    $entPhysicalDescr = array_key_exists('entPhysicalDescr', $entry) ? $entry['entPhysicalDescr'] : '';
    $entPhysicalContainedIn = array_key_exists('entPhysicalContainedIn', $entry) ? $entry['entPhysicalContainedIn'] : '';
    $entPhysicalClass = array_key_exists('entPhysicalClass', $entry) ? $entry['entPhysicalClass'] : '';
    $entPhysicalName = array_key_exists('entPhysicalName', $entry) ? $entry['entPhysicalName'] : '';
    $entPhysicalSerialNum = array_key_exists('entPhysicalSerialNum', $entry) ? $entry['entPhysicalSerialNum'] : '';
    $entPhysicalModelName = array_key_exists('entPhysicalModelName', $entry) ? $entry['entPhysicalModelName'] : '';
    $entPhysicalMfgName = array_key_exists('entPhysicalMfgName', $entry) ? $entry['entPhysicalMfgName'] : '';
    $entPhysicalVendorType = array_key_exists('entPhysicalVendorType', $entry) ? $entry['entPhysicalVendorType'] : '';
    $entPhysicalParentRelPos = array_key_exists('entPhysicalParentRelPos', $entry) ? $entry['entPhysicalParentRelPos'] : '';
    $entPhysicalHardwareRev = array_key_exists('entPhysicalHardwareRev', $entry) ? $entry['entPhysicalHardwareRev'] : '';
    $entPhysicalFirmwareRev = array_key_exists('entPhysicalFirmwareRev', $entry) ? $entry['entPhysicalFirmwareRev'] : '';
    $entPhysicalSoftwareRev = array_key_exists('entPhysicalSoftwareRev', $entry) ? $entry['entPhysicalSoftwareRev'] : '';
    $entPhysicalIsFRU = array_key_exists('entPhysicalIsFRU', $entry) ? $entry['entPhysicalIsFRU'] : '';
    $entPhysicalAlias = array_key_exists('entPhysicalAlias', $entry) ? $entry['entPhysicalAlias'] : '';
    $entPhysicalAssetID = array_key_exists('entPhysicalAssetID', $entry) ? $entry['entPhysicalAssetID'] : '';

    // List of real names for cisco entities
    $entPhysicalVendorTypes = [
        'cevC7xxxIo1feTxIsl' => 'C7200-IO-FE-MII',
        'cevChassis7140Dualfe' => 'C7140-2FE',
        'cevChassis7204' => 'C7204',
        'cevChassis7204Vxr' => 'C7204VXR',
        'cevChassis7206' => 'C7206',
        'cevChassis7206Vxr' => 'C7206VXR',
        'cevCpu7200Npe200' => 'NPE-200',
        'cevCpu7200Npe225' => 'NPE-225',
        'cevCpu7200Npe300' => 'NPE-300',
        'cevCpu7200Npe400' => 'NPE-400',
        'cevCpu7200Npeg1' => 'NPE-G1',
        'cevCpu7200Npeg2' => 'NPE-G2',
        'cevPa1feTxIsl' => 'PA-FE-TX-ISL',
        'cevPa2feTxI82543' => 'PA-2FE-TX',
        'cevPa8e' => 'PA-8E',
        'cevPaA8tX21' => 'PA-8T-X21',
        'cevMGBIC1000BaseLX' => '1000BaseLX GBIC',
        'cevPort10GigBaseLR' => '10GigBaseLR',
    ];

    if (! empty($entPhysicalVendorTypes[$entPhysicalVendorType]) && ! $entPhysicalModelName) {
        $entPhysicalModelName = $entPhysicalVendorTypes[$entPhysicalVendorType];
    }

    discover_entity_physical($valid, $device, $entPhysicalIndex, $entPhysicalDescr, $entPhysicalClass, $entPhysicalName, $entPhysicalModelName, $entPhysicalSerialNum, $entPhysicalContainedIn, $entPhysicalMfgName, $entPhysicalParentRelPos, $entPhysicalVendorType, $entPhysicalHardwareRev, $entPhysicalFirmwareRev, $entPhysicalSoftwareRev, $entPhysicalIsFRU, $entPhysicalAlias, $entPhysicalAssetID, $ifIndex);
}//end foreach
echo "\n";
unset(
    $update_data,
    $insert_data,
    $entry,
    $entity_array
);
