<?php
/*
 * LibreNMS discovery module for Eltex-MES24xx SFP inventory items
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
 * @copyright  2024 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

echo ' ELTEX-MES24xx' . PHP_EOL;
$oidSfp = SnmpQuery::cache()->hideMib()->walk('ELTEX-PHY-MIB::eltexPhyTransceiverInfoTable')->table(1);
$oidEnt = SnmpQuery::cache()->hideMib()->walk('ENTITY-MIB::entPhysicalParentRelPos')->table(1);

if (! empty($oidSfp) && ! empty($oidEnt)) {
    d_echo('ELTEX 24xx Inventory: Discovering ...' . PHP_EOL);
    $entity_array = [];
    $infoType = [0 => 'unknown', 1 => 'gbic', 2 => 'sff', 3 => 'sfp-sfpplus', 255 => 'vendorspecific'];
    $connType = [0 => 'unknown', 1 => 'SC', 7 => 'LC', 11 => 'optical-pigtail', 255 => 'vendorspecific'];

    foreach ($oidSfp as $index => $data) {
        foreach ($oidEnt as $entIndex => $entData) {
            if ($entData['entPhysicalParentRelPos'] == $index) {
                $entity_array[] = [
                    'entPhysicalIndex' => $index,
                    'entPhysicalSerialNum' => $data['eltexPhyTransceiverInfoSerialNumber'],
                    'entPhysicalModelName' => $data['eltexPhyTransceiverInfoPartNumber'],
                    'entPhysicalName' => $connType[$data['eltexPhyTransceiverInfoConnectorType']],
                    'entPhysicalDescr' => $infoType[$data['eltexPhyTransceiverInfoType']],
                    'entPhysicalClass' => 'sfp-cage',
                    'entPhysicalContainedIn' => $entIndex,
                    'entPhysicalMfgName' => $data['eltexPhyTransceiverInfoVendorName'],
                    'entPhysicalHardwareRev' => $data['eltexPhyTransceiverInfoVendorRevision'],
                    'entPhysicalIsFRU' => 'true',
                ];
                break;
            }
        }
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
