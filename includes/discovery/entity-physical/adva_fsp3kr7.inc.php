<?php
/*
 * LibreNMS module to discover hardware components in a ADVA FSP3000 R7
 *
 * Copyright (c) 2023 Khairi Azmi <mkhairi47@hotmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$adva_entphysical = snmpwalk_cache_multi_oid($device, 'advaInventoryTable', [], 'ADVA-FSPR7-MIB');
$entity_array = [];
$index = 1;

foreach ($adva_entphysical as $adva) {
    $entity_array[] = [
        'entPhysicalIndex'          => $index,
        'entPhysicalDescr'          => $adva['advaInventoryAidString'],
        'entPhysicalClass'          => $adva['advaInventoryClass'],
        'entPhysicalName'           => $adva['advaInventoryUnitName'],
        'entPhysicalModelName'      => $adva['advaInventoryPartnumber'],
        'entPhysicalSerialNum'      => $adva['advaInventoryUniversalSerialIdent'],
        'entPhysicalContainedIn'    => 0,
        'entPhysicalMfgName'        => $adva['advaInventoryVendorId'],
        'entPhysicalParentRelPos'   => -1,
        'entPhysicalVendorType'     => $adva['advaInventoryType'],
        'entPhysicalHardwareRev'    => $adva['advaInventoryHardwareRev'],
        'entPhysicalFirmwareRev'    => $adva['advaInventoryFirmwarePackageRev'],
        'entPhysicalSoftwareRev'    => $adva['advaInventorySoftwareRev'],
    ];
    $index++;
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
