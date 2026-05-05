<?php

/**
 * Bosch.php
 *
 * Bosch
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
        $response = SnmpQuery::get('BSS-RCP-MIB::serial-number.0');

        $device->serial = preg_replace('/(?<zero>0)(?<digit>\d)|(?<blank>\s)|(?<end>\X)/', '\\2', (string) $response->value('BSS-RCP-MIB::serial-number.0')) ?: null;
        /** $pattern = '/(?<zero>0)(?<digit>\d)(?<blank>\ )/';
            $replacement = '\\2';
            $subject = $response; */
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
        ]));

        return $inventory;
    }
}
