<?php

/** Boschcpp.php
 *
 * Bosch *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\StringHelpers;
use SnmpQuery;

class Boschcpp extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); //yaml

        // vendor sends strings with an off by one length (trailing null), so net-snmp returns hex
        $response = SnmpQuery::get(['BSS-RCP-MIB::software-version.0', 'BSS-RCP-MIB::oem-device-name.0']);
        $device->version = StringHelpers::decodeSnmpHexText($response->value('BSS-RCP-MIB::software-version.0'));
        $device->hardware = StringHelpers::decodeSnmpHexText($response->value('BSS-RCP-MIB::oem-device-name.0'));
        $device->sysName = rtrim($device->sysName, ".,\n,\0");

        //version shows as "24500793." instead of 7.93.0024/
        if (! empty($device->version)) {
            $digits = preg_replace('/[^0-9]/', '', $device->version);
            $padded = str_pad($digits, 8, '0', STR_PAD_LEFT);
            $chunks = str_split($padded, 2);
            $device->version = sprintf(
                '%d.%d.00%s',
                (int) ($chunks[2] ?? 0),   // Major (Index 2)
                (int) ($chunks[3] ?? 0),   // Minor (Index 3)
                $chunks[0] ?? '00'        // Build (Index 0)
            );
        }
    }

    // Device doesn't respond to Entity-MIB /
    /**
     * @return Collection<int, EntPhysical>
     */
    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;
        $serial = SnmpQuery::get('BSS-RCP-MIB::serial-number.0')->value();
        $response = SnmpQuery::get([
            'BSS-RCP-MIB::unit-name.0',
            'BSS-RCP-MIB::oem-device-name.0',
            'BSS-RCP-MIB::hardware-version.0',
            'BSS-RCP-MIB::software-version.0',
            'BSS-RCP-MIB::manufacturer-name.0',
        ]);
        $name = StringHelpers::decodeSnmpHexText($response->value('BSS-RCP-MIB::unit-name.0'));
        $model = StringHelpers::decodeSnmpHexText($response->value('BSS-RCP-MIB::oem-device-name.0'));
        $hardware = StringHelpers::decodeSnmpHexText($response->value('BSS-RCP-MIB::hardware-version.0'));
        $software = StringHelpers::decodeSnmpHexText($response->value('BSS-RCP-MIB::software-version.0'));
        $vendor = StringHelpers::decodeSnmpHexText($response->value('BSS-RCP-MIB::manufacturer-name.0'));
        $ctns = [
            'DINION IP starlight 6000 HD|F0009143' => 'NBN-6x023-B',
            'DINION IP starlight 7000 HD|F0009143' => 'NBN-7x023-BA',
            'DINION IP starlight 8000 MP|F0007143' => 'NBN-80052-BA',
            'DINION IP ultra 8000 MP|F0008C43' => 'NBN-80122-CA',
            'AUTODOME IP starlight 5000i - 2MP|F000B743' => 'NDP-5522-Z30',
            'AUTODOME IP 7000|F0005243' => 'VG5-70xx-Ex',
            'AUTODOME IP starlight 7000i|F000AA43' => 'NDP-7512-Z30',
            'AUTODOME 7100i - 2MP|F000B543' => 'NDP-7802-Z40',
            'FLEXIDOME indoor 5100i IR - 8MP|F000B543' => 'NDV-5704-AL',
            'FLEXIDOME multi 7000i - 20MP|F000B543' => 'NDM-7703-A',
            'FLEXIDOME IP starlight 7000 VR|F0009443' => 'NIN-73023-AxA',
        ];
        $key = $model . '|' . $hardware;

        $ctn = $ctns[$key] ?? null;

        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => 1,
            'entPhysicalDescr' => $model,
            'entPhysicalClass' => 1,
            'entPhysicalName' => $name,
            'entPhysicalModelName' => $ctn,
            'entPhysicalHardwareRev' => $hardware,
            'entPhysicalSoftwareRev' => $software,
            'entPhysicalSerialNum' => preg_replace('/(?<zero>0)(?<digit>\d)|(?<blank>\s)|(?<end>\X)/', '\\2', (string) $serial),
            'entPhysicalMfgName' => $vendor,
            'entPhysicalAlias' => SnmpQuery::get('BSS-RCP-MIB::mac-address.0')->value(),
        ]));

        return $inventory;
    }
}
