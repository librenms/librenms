<?php
/*
 * LibreNMS entity-physical module for the discovery of components in the Ciena Service Delivery Switch family
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo "\nCaching OIDs:";

$entity_array = [];

echo 'Ciena SDS';

// Chassis stuff
$cienaCesChassisGlobal = snmpwalk_cache_multi_oid(
    $device,
    'cienaCesChassisGlobal',
    $cienaCesChassisGlobal,
    'CIENA-CES-CHASSIS-MIB',
    'ciena'
);
$cienaCesChassisPlatform = snmpwalk_cache_multi_oid(
    $device,
    'cienaCesChassisPlatform',
    $cienaCesChassisPlatform,
    'CIENA-CES-CHASSIS-MIB',
    'ciena'
);
$cienaCesChassisIDP = snmpwalk_cache_multi_oid(
    $device,
    'cienaCesChassisIDP',
    $cienaCesChassisIDP,
    'CIENA-CES-CHASSIS-MIB',
    'ciena'
);

$chassis_array = array_replace_recursive(
    $cienaCesChassisGlobal,
    $cienaCesChassisPlatform,
    $cienaCesChassisIDP
);

// PSU Stuff
$cienaCesChassisPowerModule = snmpwalk_cache_multi_oid(
    $device,
    'cienaCesChassisPowerModule',
    $cienaCesChassisPowerModule,
    'CIENA-CES-CHASSIS-MIB',
    'ciena'
);

// Fan Stuff
$cienaCesChassisFanTrayEntry = snmpwalk_cache_multi_oid(
    $device,
    'cienaCesChassisFanTrayEntry',
    $cienaCesChassisFanTrayEntry,
    'CIENA-CES-CHASSIS-MIB',
    'ciena'
);
$cienaCesChassisFanEntry = snmpwalk_cache_multi_oid(
    $device,
    'cienaCesChassisFanEntry',
    $cienaCesChassisFanEntry,
    'CIENA-CES-CHASSIS-MIB',
    'ciena'
);
$cienaCesChassisFanTempEntry = snmpwalk_cache_multi_oid(
    $device,
    'cienaCesChassisFanTempEntry',
    $cienaCesChassisFanTempEntry,
    'CIENA-CES-CHASSIS-MIB',
    'ciena'
);

// Module Stuff
$cienaCesModuleEntry = snmpwalk_cache_multi_oid(
    $device,
    'cienaCesModuleEntry',
    $cienaCesModuleEntry,
    'CIENA-CES-MODULE-MIB',
    'ciena'
);
$cienaCesModuleDescriptionEntry = snmpwalk_cache_multi_oid(
    $device,
    'cienaCesModuleDescriptionEntry',
    $cienaCesModuleDescriptionEntry,
    'CIENA-CES-MODULE-MIB',
    'ciena'
);
$cienaCesModuleSwEntry = snmpwalk_cache_multi_oid(
    $device,
    'cienaCesModuleSwEntry',
    $cienaCesModuleSwEntry,
    'CIENA-CES-MODULE-MIB',
    'ciena'
);

$module_array = array_merge_recursive(
    $cienaCesModuleEntry,
    $cienaCesModuleDescriptionEntry,
    $cienaCesModuleSwEntry
);

// Interface stuff
$interfaceIndexMapping = snmpwalk_cache_multi_oid(
    $device,
    'dot1dBasePortIfIndex',
    $interfaceIndexMapping,
    'BRIDGE-MIB',
    'ciena'
);

$cienaCesEttpConfigEntry = snmpwalk_cache_multi_oid(
    $device,
    'cienaCesEttpConfigEntry',
    $cienaCesEttpConfigEntry,
    'CIENA-CES-PORT-MIB',
    'ciena'
);
$cienaCesPortXcvrEntry = snmpwalk_cache_multi_oid(
    $device,
    'cienaCesPortXcvrEntry',
    $cienaCesPortXcvrEntry,
    'CIENA-CES-PORT-XCVR-MIB',
    'ciena'
);

foreach ($chassis_array as $cienaCesChassis => $chassis_contents) {
    // as far as I know, there can only be 1 chassis, but iterate just in case
    $chassisIndex = $cienaCesChassis + 1;
    $entity_array[] = [
        'entPhysicalIndex'        => $chassisIndex,
        'entPhysicalDescr'        => $chassis_contents['cienaCesChassisPlatformDesc'],
        'entPhysicalClass'        => 'chassis',
        'entPhysicalName'         => 'Chassis',
        'entPhysicalModelName'    => $chassis_contents['cienaCesChassisPartNumber'],
        'entPhysicalSerialNum'    => $chassis_contents['cienaCesChassisSerialNumber'],
        'entPhysicalContainedIn'  => '0',
        'entPhysicalMfgName'      => 'Ciena',
        'entPhysicalParentRelPos' => '-1',
        'entPhysicalHardwareRev'  => $contents['cienaCesChassisIDPModelRevision'],
        'entPhysicalIsFRU'        => 'true',
    ];
    $entity_array[] = [
        'entPhysicalIndex'        => "40$chassisIndex",
        'entPhysicalClass'        => 'container',
        'entPhysicalName'         => 'Modules',
        'entPhysicalContainedIn'  => $chassisIndex,
        'entPhysicalParentRelPos' => -1,
    ];
    $entity_array[] = [
        'entPhysicalIndex'        => "41$chassisIndex",
        'entPhysicalClass'        => 'container',
        'entPhysicalName'         => 'Power Supplies',
        'entPhysicalContainedIn'  => $chassisIndex,
        'entPhysicalParentRelPos' => -1,
    ];
    $entity_array[] = [
        'entPhysicalIndex'        => "42$chassisIndex",
        'entPhysicalClass'        => 'container',
        'entPhysicalName'         => 'Fans',
        'entPhysicalContainedIn'  => $chassisIndex,
        'entPhysicalParentRelPos' => -1,
    ];
}

foreach ($cienaCesChassisPowerModule as $index => $contents) {
    $entity_array[] = [
        'entPhysicalIndex'        => "50$index",
        'entPhysicalDescr'        => $contents['cienaCesChassisPowerSupplyManufacturer'],
        'entPhysicalClass'        => 'sensor',
        'entPhysicalName'         => $contents['cienaCesChassisPowerSupplySlotName'],
        'entPhysicalModelName'    => $contents['cienaCesChassisPowerSupplyPartNum'],
        'entPhysicalSerialNum'    => $contents['cienaCesChassisPowerSupplySerialNumber'],
        'entPhysicalContainedIn'  => '41' . $contents['cienaCesChassisPowerSupplyChassisIndx'],
        'entPhysicalMfgName'      => 'Ciena',
        'entPhysicalParentRelPos' => $contents['cienaCesChassisPowerSupplySlotIndx'],
        'entPhysicalHardwareRev'  => $contents['cienaCesChassisPowerSupplyRevInfo'],
        'entPhysicalIsFRU'        => $contents['cienaCesChassisPowerSupplyFRU'],
        'ifIndex'                 => null,
    ];
}

foreach ($cienaCesChassisFanTrayEntry as $index => $contents) {
    switch ($contents['cienaCesChassisFanTrayType']) {
        case 1:
            $typeString = 'Fixed fan tray, ';
            break;
        case 2:
            $typeString = 'Hot swappable fan tray, ';
            break;
        case 3:
            $typeString = 'Unequipped fan tray, ';
            break;
        default:
            $typeString = '';
    }
    switch ($contents['cienaCesChassisFanTrayMode']) {
        case 1:
            $modeString = 'Invalid fan configuration!';
            break;
        case 2:
            $modeString = 'Fully populated';
            break;
        case 3:
            $modeString = 'Auto mode';
            break;
        default:
            $modeString = '';
    }

    $entity_array[] = [
        'entPhysicalIndex'        => "53$index",
        'entPhysicalClass'        => 'sensor',
        'entPhysicalName'         => $contents['cienaCesChassisFanTrayName'],
        'entPhysicalModelName'    => 'Fan Tray',
        'entPhysicalDescr'        => "$typeString$modeString",
        'entPhysicalSerialNum'    => $contents['cienaCesChassisFanTraySerialNumber'],
        'entPhysicalContainedIn'  => '42' . $contents['cienaCesChassisFanTrayChassisIndx'],
        'entPhysicalMfgName'      => 'Ciena',
        'entPhysicalParentRelPos' => $contents['cienaCesChassisFanTraySlotIndx'],
        'entPhysicalIsFRU'        => ($contents['cienaCesChassisFanTrayType'] = '2') ? 'true' : 'false',
    ];
}

foreach ($cienaCesChassisFanEntry as $index => $contents) {
    // index = fanTray.fanIndex
    $indexArr = explode('.', $index);
    $fanTray = $indexArr[0];
    $fanIndex = $indexArr[1];

    $entity_array[] = [
        'entPhysicalIndex'        => "51$fanIndex",
        'entPhysicalClass'        => 'sensor',
        'entPhysicalName'         => $contents['cienaCesChassisFanName'],
        'entPhysicalModelName'    => 'Fan',
        'entPhysicalContainedIn'  => (isset($cienaCesChassisFanTrayEntry[$fanTray])) ?
            "53$fanTray" : '42' . $contents['cienaCesChassisFanChassisIndx'],
        'entPhysicalMfgName'      => 'Ciena',
        'entPhysicalParentRelPos' => $fanIndex,
    ];
}

foreach ($cienaCesChassisFanTempEntry as $index => $contents) {
    // index = fanTray.sensorIndex
    $indexArr = explode('.', $index);
    $fanTray = $indexArr[0];
    $sensorIndex = $indexArr[1];

    $entity_array[] = [
        'entPhysicalIndex'        => "52$sensorIndex",
        'entPhysicalClass'        => 'sensor',
        'entPhysicalName'         => $contents['cienaCesChassisFanTempName'],
        'entPhysicalModelName'    => 'Temp Sensor',
        'entPhysicalContainedIn'  => (isset($cienaCesChassisFanTrayEntry[$fanTray])) ?
            "53$fanTray" : '42' . $contents['cienaCesChassisFanTempChassisIndx'],
        'entPhysicalParentRelPos' => -1,
    ];
}

foreach ($module_array as $index => $contents) {
    // index = chassisIndex.shelfIndex.slotIndex
    $indexArr = explode('.', $index);
    $chassisIndex = $indexArr[0];
    $shelfIndex = $indexArr[1];
    $slotIndex = $indexArr[2];

    $entity_array[] = [
        'entPhysicalIndex'        => "55$slotIndex",
        'entPhysicalDescr'        => $contents['cienaCesModuleDescription'] . ', ' . $contents['cienaCesModuleSwRunningRelease'],
        'entPhysicalClass'        => 'sensor',
        'entPhysicalName'         => $contents['cienaCesModuleSlotName'] . ': ' . $contents['cienaCesModuleDescriptionBoardName'],
        'entPhysicalModelName'    => $contents['cienaCesModuleDescriptionBoardPartNum'],
        'entPhysicalSerialNum'    => $contents['cienaCesModuleDescriptionBoardSerialNum'],
        'entPhysicalContainedIn'  => '40' . $chassisIndex,
        'entPhysicalMfgName'      => 'Ciena',
        'entPhysicalParentRelPos' => $slotIndex,
        'entPhysicalFirmwareRev'  => $contents['cienaCesModuleSwRunningRelease'],
        'entPhysicalIsFRU'        => 'true',
    ];
}

foreach ($cienaCesEttpConfigEntry as $index => $contents) {
    $portIndex = $interfaceIndexMapping[$index]['dot1dBasePortIfIndex'];
    $nameArr = explode('/', $contents['cienaCesEttpConfigName']);
    $slotIndex = ((isset($nameArr[1])) ? $nameArr[0] : 1);

    $entity_array[] = [
        'entPhysicalIndex'        => "56$index",
        'entPhysicalDescr'        => $contents['cienaCesEttpConfigEttpType'],
        'entPhysicalClass'        => 'port',
        'entPhysicalName'         => $contents['cienaCesEttpConfigName'],
        'entPhysicalContainedIn'  => '55' . $slotIndex,
        'entPhysicalParentRelPos' => $index,
        'ifIndex'                 => $portIndex,
    ];
    if (isset($cienaCesPortXcvrEntry[$index])) {
        if ($cienaCesPortXcvrEntry[$index]['cienaCesPortXcvrOperState'] != 'notPresent') {
            $wavelengthString = ($cienaCesPortXcvrEntry[$index]['cienaCesPortXcvrWaveLength'] != 0 ?
                $cienaCesPortXcvrEntry[$index]['cienaCesPortXcvrWaveLength'] . ' nm ' : '');
            $mfgString = ($cienaCesPortXcvrEntry[$index]['cienaCesPortXcvrMfgDate'] != '' ?
                'manufactured ' . $cienaCesPortXcvrEntry[$index]['cienaCesPortXcvrMfgDate'] . ' ' : '');
            $entity_array[] = [
                'entPhysicalIndex'        => $portIndex,
                'entPhysicalDescr'        => $cienaCesPortXcvrEntry[$index]['cienaCesPortXcvrVendorName'] . ' ' . $wavelengthString .
                    $cienaCesPortXcvrEntry[$index]['cienaCesPortXcvrIdentiferType'] . ' transceiver ' . $mfgString,
                'entPhysicalClass'        => 'sensor',
                'entPhysicalModelName'    => $cienaCesPortXcvrEntry[$index]['cienaCesPortXcvrVendorPartNum'],
                'entPhysicalSerialNum'    => $cienaCesPortXcvrEntry[$index]['cienaCesPortXcvrSerialNum'],
                'entPhysicalContainedIn'  => "56$index",
                'entPhysicalMfgName'      => $cienaCesPortXcvrEntry[$index]['cienaCesPortXcvrVendorName'],
                'entPhysicalParentRelPos' => -1,
                'entPhysicalHardwareRev'  => $cienaCesPortXcvrEntry[$index]['cienaCesPortXcvrRevNum'],
                'entPhysicalIsFRU'        => 'true',
            ];
        }
    }
}

foreach ($entity_array as $entPhysicalIndex => $entry) {
    discover_entity_physical(
        $valid,
        $device,
        array_key_exists('entPhysicalIndex', $entry) ? $entry['entPhysicalIndex'] : '',
        array_key_exists('entPhysicalDescr', $entry) ? $entry['entPhysicalDescr'] : '',
        array_key_exists('entPhysicalClass', $entry) ? $entry['entPhysicalClass'] : '',
        array_key_exists('entPhysicalName', $entry) ? $entry['entPhysicalName'] : '',
        array_key_exists('entPhysicalModelName', $entry) ? $entry['entPhysicalModelName'] : '',
        array_key_exists('entPhysicalSerialNum', $entry) ? $entry['entPhysicalSerialNum'] : '',
        array_key_exists('entPhysicalContainedIn', $entry) ? $entry['entPhysicalContainedIn'] : '',
        array_key_exists('entPhysicalMfgName', $entry) ? $entry['entPhysicalMfgName'] : '',
        array_key_exists('entPhysicalParentRelPos', $entry) ? $entry['entPhysicalParentRelPos'] : '',
        array_key_exists('entPhysicalVendorType', $entry) ? $entry['entPhysicalVendorType'] : '',
        array_key_exists('entPhysicalHardwareRev', $entry) ? $entry['entPhysicalHardwareRev'] : '',
        array_key_exists('entPhysicalFirmwareRev', $entry) ? $entry['entPhysicalFirmwareRev'] : '',
        array_key_exists('entPhysicalSoftwareRev', $entry) ? $entry['entPhysicalSoftwareRev'] : '',
        array_key_exists('entPhysicalIsFRU', $entry) ? $entry['entPhysicalIsFRU'] : '',
        array_key_exists('entPhysicalAlias', $entry) ? $entry['entPhysicalAlias'] : '',
        array_key_exists('entPhysicalAssetID', $entry) ? $entry['entPhysicalAssetID'] : '',
        array_key_exists('ifIndex', $entry) ? $entry['ifIndex'] : ''
    );
}

echo "\n";
unset(
    $update_data,
    $insert_data,
    $entry,
    $entity_array
);
