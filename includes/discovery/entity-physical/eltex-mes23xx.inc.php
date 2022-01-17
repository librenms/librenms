<?php
/*
 * LibreNMS discovery module for Eltex-MES23xx SFP inventory items
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 *
 * @copyright  2022 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */
echo "\nCaching OIDs:";

$entity_array = [];
echo ' ELTEX-MES23xx';
$trans = snmpwalk_cache_multi_oid($device, 'eltPhdTransceiverInfoEntry', [], 'ELTEX-MES-PHYSICAL-DESCRIPTION-MIB');
echo ' entAliasMappingIdentifier';
$mapping = snmpwalk_cache_multi_oid($device, 'entAliasMappingIdentifier', [], 'ENTITY-MIB:IF-MIB');

function normData($par = null)
{
    $tmp = str_replace([':', ' '], '', trim(strtoupper($par)));
    $ret = preg_match('/^[0-9A-F]+$/', $tmp) ? hex2str($tmp) : $par; //if string is pure hex, convert to ascii

    return $ret;
}

foreach ($trans as $index => $data) {
    unset($connectedto);
    foreach ($mapping as $ekey => $edata) {
        if ($edata['entAliasMappingIdentifier'] == 'ifIndex.' . $index) {
            $connectedto = explode('.', $ekey)[0];
        }
    }
    if ($connectedto) {
        $entity_array[] = [
            'entPhysicalIndex'        => $index,
            'entPhysicalDescr'        => $data['eltPhdTransceiverInfoType'],
            'entPhysicalClass'        => 'sfp-cage',
            'entPhysicalName'         => strtoupper($data['eltPhdTransceiverInfoConnectorType']),
            'entPhysicalModelName'    => normData($data['eltPhdTransceiverInfoPartNumber']),
            'entPhysicalSerialNum'    => $data['eltPhdTransceiverInfoSerialNumber'],
            'entPhysicalContainedIn'  => $connectedto,
            'entPhysicalMfgName'      => $data['eltPhdTransceiverInfoVendorName'],
            'entPhysicalHardwareRev'  => normData($data['eltPhdTransceiverInfoVendorRev']),
            'entPhysicalIsFRU'        => 'true',
        ];
    }
}

foreach ($entity_array as $entPhysicalIndex => $entry) {
    $entPhysicalIndex = $entry['entPhysicalIndex'] ?? '';
    $entPhysicalDescr = $entry['entPhysicalDescr'] ?? '';
    $entPhysicalClass = $entry['entPhysicalClass'] ?? '';
    $entPhysicalName = $entry['entPhysicalName'] ?? '';
    $entPhysicalModelName = $entry['entPhysicalModelName'] ?? '';
    $entPhysicalSerialNum = $entry['entPhysicalSerialNum'] ?? '';
    $entPhysicalContainedIn = $entry['entPhysicalContainedIn'] ?? '';
    $entPhysicalMfgName = $entry['entPhysicalMfgName'] ?? '';
    $entPhysicalParentRelPos = $entry['entPhysicalParentRelPos'] ?? '';
    $entPhysicalVendorType = $entry['entPhysicalVendorType'] ?? '';
    $entPhysicalHardwareRev = $entry['entPhysicalHardwareRev'] ?? '';
    $entPhysicalFirmwareRev = $entry['entPhysicalFirmwareRev'] ?? '';
    $entPhysicalSoftwareRev = $entry['entPhysicalSoftwareRev'] ?? '';
    $entPhysicalIsFRU = $entry['entPhysicalIsFRU'] ?? '';
    $entPhysicalAlias = $entry['entPhysicalAlias'] ?? '';
    $entPhysicalAssetID = $entry['entPhysicalAssetID'] ?? '';
    $ifIndex = $entry['ifIndex'] ?? '';

    discover_entity_physical(
        $valid,
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
        $ifIndex
    );
}//end foreach

echo "\n";
unset(
    $modules_array,
    $entry,
    $entity_array,
    $trans,
    $mapping
);
