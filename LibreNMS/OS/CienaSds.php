<?php

namespace LibreNMS\OS;

use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use LibreNMS\OS;
use SnmpQuery;

class CienaSds extends OS
{
    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;

        // Chassis stuff
        $chassis_info = SnmpQuery::get([
            'CIENA-CES-CHASSIS-MIB::cienaCesChassisPlatformDesc.0',
            'CIENA-CES-CHASSIS-MIB::cienaCesChassisPartNumber.0',
            'CIENA-CES-CHASSIS-MIB::cienaCesChassisSerialNumber.0',
            'CIENA-CES-CHASSIS-MIB::cienaCesChassisIDPModelRevision.0',
        ])->values();

        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => 1,
            'entPhysicalDescr' => $chassis_info['CIENA-CES-CHASSIS-MIB::cienaCesChassisPlatformDesc.0'] ?? null,
            'entPhysicalClass' => 'chassis',
            'entPhysicalName' => 'Chassis',
            'entPhysicalModelName' => $chassis_info['CIENA-CES-CHASSIS-MIB::cienaCesChassisPartNumber.0'] ?? null,
            'entPhysicalSerialNum' => $chassis_info['CIENA-CES-CHASSIS-MIB::cienaCesChassisSerialNumber.0'] ?? null,
            'entPhysicalMfgName' => 'Ciena',
            'entPhysicalHardwareRev' => $chassis_info['CIENA-CES-CHASSIS-MIB::cienaCesChassisIDPModelRevision.0'] ?? null,
            'entPhysicalIsFRU' => 'true',
        ]));

        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => 401,
            'entPhysicalClass' => 'container',
            'entPhysicalName' => 'Modules',
            'entPhysicalContainedIn' => 1,
        ]));
        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => 411,
            'entPhysicalClass' => 'container',
            'entPhysicalName' => 'Power Supplies',
            'entPhysicalContainedIn' => 1,
        ]));
        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => 421,
            'entPhysicalClass' => 'container',
            'entPhysicalName' => 'Fans',
            'entPhysicalContainedIn' => 1,
        ]));

        // PSU Stuff
        $cienaCesChassisPowerTable = SnmpQuery::hideMib()->enumStrings()->walk('CIENA-CES-CHASSIS-MIB::cienaCesChassisPowerTable')->table(1);
        foreach ($cienaCesChassisPowerTable as $index => $contents) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => "50$index",
                'entPhysicalDescr' => $contents['cienaCesChassisPowerSupplyManufacturer'] ?? null,
                'entPhysicalClass' => 'sensor',
                'entPhysicalName' => $contents['cienaCesChassisPowerSupplySlotName'] ?? null,
                'entPhysicalModelName' => $contents['cienaCesChassisPowerSupplyPartNum'] ?? null,
                'entPhysicalSerialNum' => $contents['cienaCesChassisPowerSupplySerialNumber'] ?? null,
                'entPhysicalContainedIn' => '41' . ($contents['cienaCesChassisPowerSupplyChassisIndx'] ?? null),
                'entPhysicalMfgName' => 'Ciena',
                'entPhysicalParentRelPos' => $contents['cienaCesChassisPowerSupplySlotIndx'] ?? null,
                'entPhysicalHardwareRev' => $contents['cienaCesChassisPowerSupplyRevInfo'] ?? null,
                'entPhysicalIsFRU' => $contents['cienaCesChassisPowerSupplyFRU'] ?? null,
            ]));
        }

        // Fan Stuff
        $trays = SnmpQuery::hideMib()->walk('CIENA-CES-CHASSIS-MIB::cienaCesChassisFanTrayTable')->table(1);
        foreach ($trays as $tray_index => $tray_data) {
            $typeString = match ($tray_data['cienaCesChassisFanTrayType']) {
                1 => 'Fixed fan tray, ',
                2 => 'Hot swappable fan tray, ',
                3 => 'Unequipped fan tray, ',
                default => '',
            };
            $modeString = match ($tray_data['cienaCesChassisFanTrayMode']) {
                1 => 'Invalid fan configuration!',
                2 => 'Fully populated',
                3 => 'Auto mode',
                default => '',
            };

            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => "53$tray_index",
                'entPhysicalClass' => 'sensor',
                'entPhysicalName' => $tray_data['cienaCesChassisFanTrayName'],
                'entPhysicalModelName' => 'Fan Tray',
                'entPhysicalDescr' => "$typeString$modeString",
                'entPhysicalSerialNum' => $tray_data['cienaCesChassisFanTraySerialNumber'],
                'entPhysicalContainedIn' => '42' . $tray_data['cienaCesChassisFanTrayChassisIndx'],
                'entPhysicalMfgName' => 'Ciena',
                'entPhysicalParentRelPos' => $tray_data['cienaCesChassisFanTraySlotIndx'],
                'entPhysicalIsFRU' => $tray_data['cienaCesChassisFanTrayType'] == '2' ? 'true' : 'false',
            ]));
        }

        $fans = SnmpQuery::hideMib()->walk('CIENA-CES-CHASSIS-MIB::cienaCesChassisFanTable')->table(2);
        foreach ($fans as $tray_index => $fans_data) {
            foreach ($fans_data as $fan_index => $fan_data) {
                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => "51$fan_index",
                    'entPhysicalClass' => 'sensor',
                    'entPhysicalName' => $fan_data['cienaCesChassisFanName'],
                    'entPhysicalModelName' => 'Fan',
                    'entPhysicalContainedIn' => isset($trays[$tray_index]) ?
                        "53$tray_index" : '42' . $fan_data['cienaCesChassisFanChassisIndx'],
                    'entPhysicalMfgName' => 'Ciena',
                    'entPhysicalParentRelPos' => $fan_index,
                ]));
            }
        }

        $fanTemps = SnmpQuery::hideMib()->walk('CIENA-CES-CHASSIS-MIB::cienaCesChassisFanTempTable')->table(2);
        foreach ($fanTemps as $tray_index => $temps_data) {
            foreach ($temps_data as $temp_index => $temp_data) {
                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => "52$temp_index",
                    'entPhysicalClass' => 'sensor',
                    'entPhysicalName' => $temp_data['cienaCesChassisFanTempName'],
                    'entPhysicalModelName' => 'Temp Sensor',
                    'entPhysicalContainedIn' => isset($trays[$tray_index]) ?
                        "53$tray_index" : '42' . $temp_data['cienaCesChassisFanTempChassisIndx'],
                ]));
            }
        }

        // Module Stuff
        $inventory = $inventory->merge(SnmpQuery::hideMib()->walk([
            'CIENA-CES-MODULE-MIB::cienaCesModuleTable',
            'CIENA-CES-MODULE-MIB::cienaCesModuleDescriptionTable',
            'CIENA-CES-MODULE-MIB::cienaCesModuleSwTable',
        ])->mapTable(function ($contents, $chassisIndex, $shelfIndex, $slotIndex) {
            $descr = $contents['cienaCesModuleDescription'];
            $release = $contents['cienaCesModuleSwRunningRelease'] ?? null;
            if ($release) {
                $descr .= ", $release";
            }

            return new EntPhysical([
                'entPhysicalIndex' => "55$slotIndex",
                'entPhysicalDescr' => $descr,
                'entPhysicalClass' => 'sensor',
                'entPhysicalName' => $contents['cienaCesModuleSlotName'] . ': ' . $contents['cienaCesModuleDescriptionBoardName'],
                'entPhysicalModelName' => $contents['cienaCesModuleDescriptionBoardPartNum'],
                'entPhysicalSerialNum' => $contents['cienaCesModuleDescriptionBoardSerialNum'],
                'entPhysicalContainedIn' => '40' . $chassisIndex,
                'entPhysicalMfgName' => 'Ciena',
                'entPhysicalParentRelPos' => $slotIndex,
                'entPhysicalFirmwareRev' => $release,
                'entPhysicalIsFRU' => 'true',
            ]);
        }));

        // Interface stuff
        $transceivers = SnmpQuery::hideMib()->enumStrings()->walk([
            'CIENA-CES-PORT-MIB::cienaCesEttpConfigTable',
            'CIENA-CES-PORT-XCVR-MIB::cienaCesPortXcvrTable',
        ])->table(1);

        foreach ($transceivers as $index => $contents) {
            if (! empty($contents['cienaCesEttpConfigName'])) {
                $nameArr = explode('/', $contents['cienaCesEttpConfigName']);
                $slotIndex = isset($nameArr[1]) ? $nameArr[0] : 1;

                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => "56$index",
                    'entPhysicalDescr' => $contents['cienaCesEttpConfigEttpType'],
                    'entPhysicalClass' => 'port',
                    'entPhysicalName' => $contents['cienaCesEttpConfigName'],
                    'entPhysicalContainedIn' => '55' . $slotIndex,
                    'entPhysicalParentRelPos' => $index,
                    'ifIndex' => $index,
                ]));
            }

            if (isset($contents['cienaCesPortXcvrOperState']) && $contents['cienaCesPortXcvrOperState'] != 'notPresent') {
                $wavelengthString = ($contents['cienaCesPortXcvrWaveLength'] != 0 ?
                    $contents['cienaCesPortXcvrWaveLength'] . ' nm ' : '');
                $mfgString = ($contents['cienaCesPortXcvrMfgDate'] != '' ?
                    'manufactured ' . $contents['cienaCesPortXcvrMfgDate'] . ' ' : '');
                $xcvrIndex = '57' . $contents['cienaCesPortXcvrPortNumber'];

                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => $xcvrIndex,
                    'entPhysicalDescr' => 'port ' . $contents['cienaCesPortXcvrPortNumber'] . ' ' . $wavelengthString .
                        ' transceiver ' . $mfgString,
                    'entPhysicalClass' => 'sensor',
                    'entPhysicalModelName' => $contents['cienaCesPortXcvrVendorPartNum'],
                    'entPhysicalSerialNum' => $contents['cienaCesPortXcvrSerialNum'],
                    'entPhysicalContainedIn' => "56$index",
                    'entPhysicalMfgName' => $contents['cienaCesPortXcvrVendorName'],
                    'entPhysicalParentRelPos' => -1,
                    'entPhysicalHardwareRev' => $contents['cienaCesPortXcvrRevNum'],
                    'entPhysicalIsFRU' => 'true',
                ]));
            }
        }

        return $inventory;
    }
}
