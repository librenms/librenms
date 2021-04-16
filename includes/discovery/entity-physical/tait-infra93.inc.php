<?php
/*
 * LibreNMS entity-physical module for the discovery of components in the MRV® OptiDriver® Optical Transport Platform
 *
 * Copyright (c) 2020 Opalivan
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo "\nCaching OIDs:";

$entity_array = [];
echo ' TAIT Infra 93';
$modules_array = snmpwalk_cache_multi_oid($device, 'TAIT-INFRA93SERIES-MIB::modules', [], 'TAIT-INFRA93SERIES-MIB');

d_echo($modules_array);

// Create a fake Chassis to host the modules we discover
$entity_array[] = [
    'entPhysicalIndex'        => '10',
    'entPhysicalDescr'        => 'Chassis',
    'entPhysicalClass'        => 'chassis',
    'entPhysicalName'         => 'Chassis',
    'entPhysicalModelName'    => 'Infra93',
    'entPhysicalContainedIn'  => '0',
    'entPhysicalMfgName'      => 'TAIT',
    'entPhysicalIsFRU'        => 'false',
];

// Fill the different modules the "entPhysical" way to have a correct display.
// We suppose only one FrontPanel, PA, PMU and Reciter is returned. If more than one is,
// this code would need to be adapted with loops
if (isset($modules_array[0]) and isset($modules_array[0]['fpInfoProductCode'])) {
    $entity_array[] = [
        'entPhysicalIndex'        => '11',
        'entPhysicalDescr'        => 'Front Panel',
        'entPhysicalClass'        => 'module',
        'entPhysicalName'         => 'Front Panel',
        'entPhysicalModelName'    => $modules_array[0]['fpInfoProductCode'],
        'entPhysicalSerialNum'    => $modules_array[0]['fpInfoSerialNumber'],
        'entPhysicalContainedIn'  => '10',
        'entPhysicalMfgName'      => 'TAIT',
        'entPhysicalHardwareRev'  => $modules_array[0]['fpInfoHardwareVersion'],
        'entPhysicalFirmwareRev'  => $modules_array[0]['fpInfoFirmwareVersion'],
        'entPhysicalIsFRU'        => 'true',
    ];
}

if (isset($modules_array[0]) and isset($modules_array[0]['rctInfoProductCode'])) {
    $entity_array[] = [
        'entPhysicalIndex'        => '120',
        'entPhysicalDescr'        => 'Reciter',
        'entPhysicalClass'        => 'module',
        'entPhysicalName'         => 'Reciter',
        'entPhysicalModelName'    => $modules_array[0]['rctInfoProductCode'],
        'entPhysicalSerialNum'    => $modules_array[0]['rctInfoSerialNumber'],
        'entPhysicalContainedIn'  => '10',
        'entPhysicalMfgName'      => 'TAIT',
        'entPhysicalHardwareRev'  => $modules_array[0]['rctInfoHardwareVersion'],
        'entPhysicalFirmwareRev'  => $modules_array[0]['rctInfoFirmwareVersion'],
        'entPhysicalIsFRU'        => 'true',
    ];
    $entity_array[] = [
        'entPhysicalIndex'        => '1200',
        'entPhysicalDescr'        => 'Reciter Temperature Sensor',
        'entPhysicalClass'        => 'sensor',
        'entPhysicalName'         => 'Reciter Temperature',
        'entPhysicalContainedIn'  => '120',
        'entPhysicalMfgName'      => 'TAIT',
        'entPhysicalParentRelPos' => '-1',
        'entPhysicalIsFRU'        => 'false',
    ];
}

if (isset($modules_array[0]) and isset($modules_array[0]['paInfoProductCode'])) {
    $entity_array[] = [
        'entPhysicalIndex'        => '130',
        'entPhysicalDescr'        => 'Power Amplifier',
        'entPhysicalClass'        => 'module',
        'entPhysicalName'         => 'Power Amplifier',
        'entPhysicalModelName'    => $modules_array[0]['paInfoProductCode'],
        'entPhysicalSerialNum'    => $modules_array[0]['paInfoSerialNumber'],
        'entPhysicalContainedIn'  => '10',
        'entPhysicalMfgName'      => 'TAIT',
        'entPhysicalHardwareRev'  => $modules_array[0]['paInfoHardwareVersion'],
        'entPhysicalFirmwareRev'  => $modules_array[0]['paInfoFirmwareVersion'],
        'entPhysicalIsFRU'        => 'true',
    ];
    $entity_array[] = [
        'entPhysicalIndex'        => '1300',
        'entPhysicalDescr'        => 'Amplifier Power Sensor',
        'entPhysicalClass'        => 'sensor',
        'entPhysicalName'         => 'Output Power',
        'entPhysicalContainedIn'  => '130',
        'entPhysicalMfgName'      => 'TAIT',
        'entPhysicalParentRelPos' => '-1',
        'entPhysicalIsFRU'        => 'false',
    ];
}

$entity_array[] = [
    'entPhysicalIndex'        => '140',
    'entPhysicalDescr'        => 'PMU',
    'entPhysicalClass'        => 'module',
    'entPhysicalName'         => 'PMU',
    'entPhysicalModelName'    => $modules_array[0]['pmuInfoProductCode'],
    'entPhysicalSerialNum'    => $modules_array[0]['pmuInfoSerialNumber'],
    'entPhysicalContainedIn'  => '10',
    'entPhysicalMfgName'      => 'TAIT',
    'entPhysicalParentRelPos' => '',
    'entPhysicalVendorType'   => '',
    'entPhysicalHardwareRev'  => $modules_array[0]['pmuInfoHardwareVersion'],
    'entPhysicalFirmwareRev'  => $modules_array[0]['pmuInfoFirmwareVersion'],
    'entPhysicalIsFRU'        => 'true',
];

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
    $modules_array,
    $entry,
    $entity_array
);
