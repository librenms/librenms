<?php

echo "\nCaching OIDs:";

if ($device['os'] == 'timos') {
    $entity_array = [];
    echo 'tmnxHwObjs';
    $entity_array = snmpwalk_cache_multi_oid($device, 'tmnxHwObjs', $entity_array, 'TIMETRA-CHASSIS-MIB', 'nokia');
} else {
    $entity_array = [];
    echo ' entPhysicalEntry';
    $entity_array = snmpwalk_cache_oid($device, 'entPhysicalEntry', $entity_array, 'ENTITY-MIB:CISCO-ENTITY-VENDORTYPE-OID-MIB');

    if (! empty($entity_array)) {
        echo ' entAliasMappingIdentifier';
        $entity_array = snmpwalk_cache_twopart_oid($device, 'entAliasMappingIdentifier', $entity_array, 'ENTITY-MIB:IF-MIB');
    }
}
if ($device['os'] == 'vrp') {
    echo ' hwEntityBoardType';
    $entity_array = snmpwalk_cache_oid($device, 'hwEntityBoardType', $entity_array, 'ENTITY-MIB:HUAWEI-ENTITY-EXTENT-MIB');
    echo ' hwEntityBomEnDesc';
    $entity_array = snmpwalk_cache_oid($device, 'hwEntityBomEnDesc', $entity_array, 'ENTITY-MIB:HUAWEI-ENTITY-EXTENT-MIB');
}

foreach ($entity_array as $entPhysicalIndex => $entry) {
    $ifIndex = 0;
    if ($device['os'] == 'timos') {
        $entPhysicalDescr = $entry['tmnxCardTypeDescription'];
        $entPhysicalContainedIn = $entry['tmnxHwContainedIn'];
        $entPhysicalClass = $entry['tmnxHwClass'];
        $entPhysicalName = $entry['tmnxCardTypeName'];
        $entPhysicalSerialNum = $entry['tmnxHwSerialNumber'];
        $entPhysicalModelName = $entry['tmnxHwMfgBoardNumber'];
        $entPhysicalMfgName = $entry['tmnxHwMfgBoardNumber'];
        $entPhysicalVendorType = $entry['tmnxCardTypeName'];
        $entPhysicalParentRelPos = $entry['tmnxHwParentRelPos'];
        $entPhysicalHardwareRev = '1.0';
        $entPhysicalFirmwareRev = $entry['tmnxHwBootCodeVersion'];
        $entPhysicalSoftwareRev = $entry['tmnxHwBootCodeVersion'];
        $entPhysicalIsFRU = $entry['tmnxHwIsFRU'];
        $entPhysicalAlias = $entry['tmnxHwAlias'];
        $entPhysicalAssetID = $entry['tmnxHwAssetID'];
        $entPhysicalIndex = str_replace('.', '', $entPhysicalIndex);
    } elseif ($device['os'] == 'vrp') {
        //Add some details collected in the VRP Entity Mib
        $entPhysicalDescr = $entry['hwEntityBomEnDesc'];
        $entPhysicalContainedIn = $entry['entPhysicalContainedIn'];
        $entPhysicalClass = $entry['entPhysicalClass'];
        $entPhysicalName = $entry['entPhysicalName'];
        $entPhysicalSerialNum = $entry['entPhysicalSerialNum'];
        $entPhysicalModelName = $entry['hwEntityBoardType'];
        $entPhysicalMfgName = $entry['entPhysicalMfgName'];
        $entPhysicalVendorType = $entry['entPhysicalVendorType'];
        $entPhysicalParentRelPos = $entry['entPhysicalParentRelPos'];
        $entPhysicalHardwareRev = $entry['entPhysicalHardwareRev'];
        $entPhysicalFirmwareRev = $entry['entPhysicalFirmwareRev'];
        $entPhysicalSoftwareRev = $entry['entPhysicalSoftwareRev'];
        $entPhysicalIsFRU = $entry['entPhysicalIsFRU'];
        $entPhysicalAlias = $entry['entPhysicalAlias'];
        $entPhysicalAssetID = $entry['entPhysicalAssetID'];

        //VRP devices seems to use LogicalEntity '1' instead of '0' like the default code checks.
        //Standard code is still run after anyway.
        if (isset($entry['1']['entAliasMappingIdentifier'])) {
            $ifIndex = preg_replace('/ifIndex\.(\d+).*/', '$1', $entry['1']['entAliasMappingIdentifier']);
        }
    } else {
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
    }//end if

    if ($device['os'] == 'dnos' && $entPhysicalSerialNum == 'NA' && preg_match('/Unit/', $entPhysicalName)) {
        $entPhysicalSerialNum = snmp_get($device, '.1.3.6.1.4.1.674.10895.3000.1.2.100.8.1.4.' . preg_replace('/Unit (\d+)/', '$1', $entPhysicalName), '-Oqv', '');
    }

    if (isset($entity_array[$entPhysicalIndex]['0']['entAliasMappingIdentifier'])) {
        $ifIndex = $entity_array[$entPhysicalIndex]['0']['entAliasMappingIdentifier'];
        if (! strpos($ifIndex, 'fIndex') || $ifIndex == '') {
            unset($ifIndex);
        } else {
            $ifIndex_array = explode('.', $ifIndex);
            $ifIndex = $ifIndex_array[1];
            unset($ifIndex_array);
        }
    }

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
