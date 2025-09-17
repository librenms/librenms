<?php

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\EntPhysical;
use App\Models\Location;

use LibreNMS\OS;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Data\DataStorageInterface;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

use SnmpQuery;

class Enexus extends OS implements OSDiscovery
{

    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
    }

    public function fetchLocation(): Location
    {
        $location = parent::fetchLocation();

        $latInt = (int) SnmpQuery::get('SP2-MIB::powerSystemLatitude.0')->value();
        $latFrac = (int) SnmpQuery::get('SP2-MIB::powerSystemLatitudeDecimal.0')->value();
        $longInt = (int) SnmpQuery::get('SP2-MIB::powerSystemLongitude.0')->value();
        $longFrac = (int) SnmpQuery::get('SP2-MIB::powerSystemLongitudeDecimal.0')->value();

        $lat = null;
        $long = null;

        if ($latInt !== null && $latFrac !== null) {
            $lat = $latInt + ($latFrac / 1000000);
        }

        if ($longInt !== null && $longFrac !== null) {
            $long = $longInt + ($longFrac / 1000000);
        }

        $location->lng = $long !== null ? (float) $long : null;
        $location->lat = $lat !== null ? (float) $lat : null;

        return $location;
    }


    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;
        // Create a fake Chassis to host the modules we discover
        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => 10,
            'entPhysicalDescr' => 'Chassis',
            'entPhysicalClass' => 'chassis',
            'entPhysicalName' => 'Chassis',
            'entPhysicalModelName' => 'Eltek',
            'entPhysicalSerialNum' => SnmpQuery::get('SP2-MIB::powerSystemSerialNumber.0')->value(),
            'entPhysicalContainedIn' => 0,
            'entPhysicalParentRelPos' => 0,
            'entPhysicalMfgName' => 'Eltek',
            'entPhysicalIsFRU' => 'false',
        ]));


        $controlUnits = SnmpQuery::walk('SP2-MIB::controlUnitTable')->table(1);
        foreach ($controlUnits as $controlUnitIndex => $controlUnit) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => $controlUnitIndex + 1000,
                'entPhysicalDescr' => 'Front Panel',
                'entPhysicalClass' => 'module',
                'entPhysicalName' => $controlUnit['SP2-MIB::controlUnitDescription'] ?? null,
                'entPhysicalModelName' => $controlUnit['SP2-MIB::controlUnitHwPartNumber'] ?? null,
                'entPhysicalSerialNum' => $controlUnit['SP2-MIB::controlUnitSerialNumber'] ?? null,
                'entPhysicalContainedIn' => 10,
                'entPhysicalMfgName' => 'Eltek',
                'entPhysicalParentRelPos' => $controlUnitIndex,
                'entPhysicalHardwareRev' => $controlUnit['SP2-MIB::controlUnitHwVersion'] ?? null,
                'entPhysicalSoftwareRev' => $controlUnit['SP2-MIB::controlUnitSwVersion'] ?? null,
                'entPhysicalFirmwareRev' => null,
                'entPhysicalIsFRU' => 'true'
            ]));
        }

        $rectifiers = SnmpQuery::walk('SP2-MIB::rectifierTable')->table(1);
        foreach ($rectifiers as $rectifierIndex => $rectifier) {
            Log::debug('rectifierIndex: ' . $rectifierIndex);
            Log::debug('rectifier: ' . json_encode($rectifier));
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' =>  $rectifierIndex,
                'entPhysicalDescr' => 'Rectifier ' . $rectifier['SP2-MIB::rectifierType'] ?? null,
                'entPhysicalClass' => 'module',
                'entPhysicalName' => $rectifier['SP2-MIB::rectifierType'] ?? null,
                'entPhysicalModelName' => $rectifier['SP2-MIB::rectifierHwPartNumber'] ?? null,
                'entPhysicalSerialNum' => $rectifier['SP2-MIB::rectifierEntry.10'] ?? null, //  rectifierSerialNumber missing in SP2-MIB
                'entPhysicalContainedIn' => 10,
                'entPhysicalMfgName' => 'Eltek',
                'entPhysicalParentRelPos' => $rectifierIndex,
                'entPhysicalHardwareRev' => $rectifier['SP2-MIB::rectifierHwVersion'] ?? null,
                'entPhysicalSoftwareRev' => $rectifier['SP2-MIB::rectifierSwVersion'] ?? null,
                'entPhysicalFirmwareRev' => null,
                'entPhysicalIsFRU' => 'true'
            ]));
        }

        $batteryInstalledType = SnmpQuery::get('SP2-MIB::batteryDescription.0')->value();
        $inventory->push(new EntPhysical([
            'entPhysicalIndex' =>  100,
            'entPhysicalDescr' => 'Battery',
            'entPhysicalClass' => 'module',
            'entPhysicalName' => $batteryInstalledType,
            'entPhysicalModelName' => $batteryInstalledType,
            'entPhysicalSerialNum' => SnmpQuery::get('SP2-MIB::batterySerialNumber.0')->value(),
            'entPhysicalContainedIn' => 10,
            'entPhysicalMfgName' => 'Eltek',
            'entPhysicalParentRelPos' => 100,
            'entPhysicalHardwareRev' => null,
            'entPhysicalSoftwareRev' => null,
            'entPhysicalFirmwareRev' => null,
            'entPhysicalIsFRU' => 'true'
        ]));

        return $inventory;
    }
}