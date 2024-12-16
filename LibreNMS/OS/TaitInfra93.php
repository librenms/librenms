<?php

namespace LibreNMS\OS;

use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use LibreNMS\OS;

class TaitInfra93 extends OS
{
    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;

        $response = \SnmpQuery::walk('TAIT-INFRA93SERIES-MIB::modules');
        if (! $response->isValid()) {
            return $inventory;
        }

        $modules = $response->table(1);

        // Create a fake Chassis to host the modules we discover
        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => 10,
            'entPhysicalDescr' => 'Chassis',
            'entPhysicalClass' => 'chassis',
            'entPhysicalName' => 'Chassis',
            'entPhysicalModelName' => 'Infra93',
            'entPhysicalContainedIn' => 0,
            'entPhysicalParentRelPos' => 0,
            'entPhysicalMfgName' => 'TAIT',
            'entPhysicalIsFRU' => 'false',
        ]));

        // Fill the different modules the "entPhysical" way to have a correct display.
        // We suppose only one FrontPanel, PA, PMU and Reciter is returned.
        if (isset($modules[0]['TAIT-INFRA93SERIES-MIB::fpInfoProductCode'])) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 11,
                'entPhysicalDescr' => 'Front Panel',
                'entPhysicalClass' => 'module',
                'entPhysicalName' => 'Front Panel',
                'entPhysicalModelName' => $modules[0]['TAIT-INFRA93SERIES-MIB::fpInfoProductCode'],
                'entPhysicalSerialNum' => $modules[0]['TAIT-INFRA93SERIES-MIB::fpInfoSerialNumber'],
                'entPhysicalContainedIn' => 10,
                'entPhysicalMfgName' => 'TAIT',
                'entPhysicalHardwareRev' => $modules[0]['TAIT-INFRA93SERIES-MIB::fpInfoHardwareVersion'],
                'entPhysicalFirmwareRev' => $modules[0]['TAIT-INFRA93SERIES-MIB::fpInfoFirmwareVersion'],
                'entPhysicalIsFRU' => 'true',
            ]));
        }

        if (isset($modules[0]['TAIT-INFRA93SERIES-MIB::rctInfoProductCode'])) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 120,
                'entPhysicalDescr' => 'Reciter',
                'entPhysicalClass' => 'module',
                'entPhysicalName' => 'Reciter',
                'entPhysicalModelName' => $modules[0]['TAIT-INFRA93SERIES-MIB::rctInfoProductCode'],
                'entPhysicalSerialNum' => $modules[0]['TAIT-INFRA93SERIES-MIB::rctInfoSerialNumber'],
                'entPhysicalContainedIn' => 10,
                'entPhysicalMfgName' => 'TAIT',
                'entPhysicalHardwareRev' => $modules[0]['TAIT-INFRA93SERIES-MIB::rctInfoHardwareVersion'],
                'entPhysicalFirmwareRev' => $modules[0]['TAIT-INFRA93SERIES-MIB::rctInfoFirmwareVersion'],
                'entPhysicalIsFRU' => 'true',
            ]));
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 1200,
                'entPhysicalDescr' => 'Reciter Temperature Sensor',
                'entPhysicalClass' => 'sensor',
                'entPhysicalName' => 'Reciter Temperature',
                'entPhysicalContainedIn' => '120',
                'entPhysicalMfgName' => 'TAIT',
                'entPhysicalIsFRU' => 'false',
            ]));
        }

        if (isset($modules[0]['TAIT-INFRA93SERIES-MIB::paInfoProductCode'])) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 130,
                'entPhysicalDescr' => 'Power Amplifier',
                'entPhysicalClass' => 'module',
                'entPhysicalName' => 'Power Amplifier',
                'entPhysicalModelName' => $modules[0]['TAIT-INFRA93SERIES-MIB::paInfoProductCode'],
                'entPhysicalSerialNum' => $modules[0]['TAIT-INFRA93SERIES-MIB::paInfoSerialNumber'],
                'entPhysicalContainedIn' => 10,
                'entPhysicalMfgName' => 'TAIT',
                'entPhysicalHardwareRev' => $modules[0]['TAIT-INFRA93SERIES-MIB::paInfoHardwareVersion'],
                'entPhysicalFirmwareRev' => $modules[0]['TAIT-INFRA93SERIES-MIB::paInfoFirmwareVersion'],
                'entPhysicalIsFRU' => 'true',
            ]));
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 1300,
                'entPhysicalDescr' => 'Amplifier Power Sensor',
                'entPhysicalClass' => 'sensor',
                'entPhysicalName' => 'Output Power',
                'entPhysicalContainedIn' => 130,
                'entPhysicalMfgName' => 'TAIT',
                'entPhysicalIsFRU' => 'false',
            ]));
        }

        if (isset($modules[0]['TAIT-INFRA93SERIES-MIB::pmuInfoProductCode'])) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 140,
                'entPhysicalDescr' => 'PMU',
                'entPhysicalClass' => 'module',
                'entPhysicalName' => 'PMU',
                'entPhysicalModelName' => $modules[0]['TAIT-INFRA93SERIES-MIB::pmuInfoProductCode'],
                'entPhysicalSerialNum' => $modules[0]['TAIT-INFRA93SERIES-MIB::pmuInfoSerialNumber'],
                'entPhysicalContainedIn' => 10,
                'entPhysicalMfgName' => 'TAIT',
                'entPhysicalHardwareRev' => $modules[0]['TAIT-INFRA93SERIES-MIB::pmuInfoHardwareVersion'],
                'entPhysicalFirmwareRev' => $modules[0]['TAIT-INFRA93SERIES-MIB::pmuInfoFirmwareVersion'],
                'entPhysicalIsFRU' => 'true',
            ]));
        }

        return $inventory;
    }
}
