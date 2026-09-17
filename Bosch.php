<?php

/** Bosch.php
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
use SnmpQuery;

class Bosch extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); //yaml

        $device->serial = preg_replace('/(?<zero>0)(?<digit>\d)|(?<blank>\s)|(?<end>\X)/', '\\2', $device->serial);

    }

    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;
        $response = SnmpQuery::get('BSS-RCP-MIB::serial-number.0');
        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => 1,
            'entPhysicalDescr' => SnmpQuery::get('BSS-RCP-MIB::oem-device-name.0')->value(),
            'entPhysicalClass' => SnmpQuery::get('ENTITY-MIB::entPhysicalClass.1')->value(),
            'entPhysicalName' => SnmpQuery::get('BSS-RCP-MIB::unit-name.0')->value(),
            'entPhysicalModelName' => SnmpQuery::get('BSS-RCP-MIB::oem-device-name.0')->value(),
            'entPhysicalSerialNum' => preg_replace('/(?<zero>0)(?<digit>\d)|(?<blank>\s)|(?<end>\X)/', '\\2', (string) $response->value('BSS-RCP-MIB::serial-number.0')),
            'entPhysicalMfgName' => SnmpQuery::get('BSS-RCP-MIB::manufacturer-name.0')->value(),
            'entPhysicalAlias' => SnmpQuery::get('BSS-RCP-MIB::mac-address.0')->value(),
        ]));

        return $inventory;
    }
}
/**HW F0009143  = DINION IP starlight 7000HD      = NBN-7x023-BA
 * HW F0007143  = DINION IP starlight 8000MP      = NBN-80052-BA
 * HW F0008C43  = DINION IP ultra 8000MP          = NBN-80122-CA
 * HW F000B543  = FLEXIDOME multi 7000i -20MP     = NDM-7703-A
 * HW F0009443  = FLEXIDOME IP starlight 7000 VR  = NIN-73023-AxA
 * HW F000B543  = FLEXIDOME indoor 5100i IR - 8MP = NDV-5704-AL
 * HW F000AA43  = AUTODOME IP starlight 7000i     = NDP-7512-Z30
 * HW F0005243  = AUTODOME 7000 IP                = VG5-70xx-Ex
 */
