<?php
/**
 * ciena-rls.inc.php
 *
 * -Description-
 *
 * Chassis inventory for a Ciena Reconfigurable Line System (RLS).
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
 * Traps when Adva objects are created. This includes Remote User Login object,
 * Flow Creation object, and LAG Creation object.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2024 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */
$entity_array = [];

$inventory = snmpwalk_cache_multi_oid($device, 'rlsCircuitPackTable', [], 'CIENA-6500R-INVENTORY-MIB');

foreach ($inventory as $inventory => $inventoryItems) {
    $entity_array[] = [
        'entPhysicalIndex' => $inventory, //need to derive index from the oid
        'entPhysicalDescr' => $inventoryItems['rlsCircuitPackCtype'],
        'entPhysicalName' => $inventoryItems['rlsCircuitPackCtype'],
        'entPhysicalModelName' => $inventoryItems['rlsCircuitPackPec'],
        'entPhysicalSerialNum' => $inventoryItems['rlsCircuitPackSerialNumber'],
        'entPhysicalParentRelPos' => $inventory,
        'entPhysicalMfgName' => 'Ciena',
        'entPhysicalAlias' => $inventoryItems['rlsCircuitPackCommonLanguageEquipmentIndentifier'],
        'entPhysicalHardwareRev' => $inventoryItems['rlsCircuitPackHardwareRelease'],
        'entPhysicalIsFRU' => 'true',
    ];
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
    $inventory,
    $entity_array
);
