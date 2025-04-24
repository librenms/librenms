<?php

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\StringHelpers;
use SnmpQuery;

class Eurostor extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $info = SnmpQuery::get([
            'proware-SNMP-MIB::siVendor.0',
            'proware-SNMP-MIB::siModel.0',
            'proware-SNMP-MIB::siSerial.0',
            'proware-SNMP-MIB::siFirmVer.0',
        ])->values();

        $device->version = $info['proware-SNMP-MIB::siFirmVer.0'] ?? null;
        $device->hardware = $info['proware-SNMP-MIB::siModel.0'] ?? null;
        $device->features = $info['proware-SNMP-MIB::siVendor.0'] ?? null;
        $device->serial = $info['proware-SNMP-MIB::siSerial.0'] ?? null;

        if (preg_match('/^ES/', $device->hardware)) {
            $device->hardware = 'EUROstore [' . $device->hardware . ']';
        }

        if (preg_match('/^ARC/', $device->features)) {
            $device->features = 'Controller: Areca ' . $device->features;
        }

        // Sometimes firmware outputs serial as hex-string
        if (StringHelpers::isHex($device->serial)) {
            $device->serial = StringHelpers::hexToAscii($device->serial, ' ');
        }
    }

    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;
        $id = 1;

        $chassis_array = SnmpQuery::hideMib()->walk('proware-SNMP-MIB::systeminformation')->table(1);
        foreach ($chassis_array as $chassis_contents) {
            // Discover the chassis
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => $id++,
                'entPhysicalDescr' => "Eurostore {$chassis_contents['siModel']}",
                'entPhysicalClass' => 'chassis',
                'entPhysicalModelName' => $chassis_contents['siModel'],
                'entPhysicalSerialNum' => $chassis_contents['siSerial'],
                'entPhysicalContainedIn' => '0',
                'entPhysicalVendorType' => $chassis_contents['siVendor'],
                'entPhysicalHardwareRev' => $chassis_contents['siBootVer'],
                'entPhysicalFirmwareRev' => $chassis_contents['siFirmVer'],
            ]));
        }

        for ($i = 1; $i <= 8; $i++) {
            $backplane_array = SnmpQuery::hideMib()->walk('proware-SNMP-MIB::hwEnclosure' . $i)->table(1);

            foreach ($backplane_array as $backplane_contents) {
                if (empty($backplane_contents['hwEnclosure0' . $i . 'Installed']) || $backplane_contents['hwEnclosure0' . $i . 'Installed'] != 2) {
                    continue;
                }
                $backplane_id = $id++;
                // Discover the chassis
                $inventory->push(new EntPhysical([
                    'entPhysicalIndex' => $backplane_id,
                    'entPhysicalDescr' => $backplane_contents['hwEnclosure0' . $i . 'Description'],
                    'entPhysicalClass' => 'backplane',
                    'entPhysicalContainedIn' => '1',
                    'entPhysicalParentRelPos' => $i,
                ]));

                $hdd_array = SnmpQuery::hideMib()->walk('proware-SNMP-MIB::hddEnclosure0' . $i . 'InfoTable')->table(1);
                foreach ($hdd_array as $hdd_contents) {
                    // Discover the chassis
                    $inventory->push(new EntPhysical([
                        'entPhysicalContainedIn' => $backplane_id,
                        'entPhysicalIndex' => $id++,
                        'entPhysicalDescr' => $hdd_contents['hddEnclosure0' . $i . 'Desc'],
                        'entPhysicalClass' => 'container',
                        'entPhysicalParentRelPos' => $hdd_contents['hddEnclosure0' . $i . 'Slots'],
                        'entPhysicalName' => $hdd_contents['hddEnclosure0' . $i . 'Name'],
                        'entPhysicalSerialNum' => $hdd_contents['hddEnclosure0' . $i . 'Serial'],
                        'entPhysicalFirmwareRev' => $hdd_contents['hddEnclosure0' . $i . 'FirmVer'],
                        'entPhysicalIsFRU' => 'true',
                        'entPhysicalAlias' => $hdd_contents['hddEnclosure0' . $i . 'State'],
                    ]));
                }
            }
        }

        return $inventory;
    }
}
