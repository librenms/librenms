<?php

/**
 * Awplus.php
 *
 * -Description-
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
 * @link       https://www.librenms.org
 *
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Awplus extends OS implements OSDiscovery, TransceiverDiscovery
{
    public function discoverOS(Device $device): void
    {
        //$hardware and $serial use first return as the OID for these is not always fixed.
        //However, the first OID is the device baseboard.

        $response = SnmpQuery::walk(['AT-RESOURCE-MIB::rscBoardName', 'AT-RESOURCE-MIB::rscBoardSerialNumber']);
        $hardware = $response->value('AT-RESOURCE-MIB::rscBoardName');
        $serial = $response->value('AT-RESOURCE-MIB::rscBoardSerialNumber');

        // SBx8100 platform has line cards show up first in "rscBoardName" above.
        //Instead use sysObjectID.0

        if (Str::contains($hardware, 'SBx81')) {
            $hardware = SnmpQuery::hideMib()->mibs(['AT-PRODUCT-MIB'])->translate($device->sysObjectID);
            $hardware = str_replace('at', 'AT-', $hardware);

            // Features and Serial is set to Controller card 1.5 or 1.6
            $features = $response->value([
                'AT-RESOURCE-MIB::rscBoardName.5.6',
                'AT-RESOURCE-MIB::rscBoardName.6.6',
            ]);
            $serial = $response->value([
                'AT-RESOURCE-MIB::rscBoardSerialNumber.5.6',
                'AT-RESOURCE-MIB::rscBoardSerialNumber.6.6',
            ]);
        }

        $device->version = SnmpQuery::get('AT-SETUP-MIB::currSoftVersion.0')->value();
        $device->serial = $serial;
        $device->hardware = $hardware;
        $device->features = $features ?? null;
    }

    public function discoverTransceivers(): Collection
    {
        return \SnmpQuery::enumStrings()->walk('AT-SYSINFO-MIB::atPortInfoTransceiverTable')
            ->mapTable(function ($data, $ifIndex) {
                return new Transceiver([
                    'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                    'index' => $ifIndex,
                    'type' => $data['AT-SYSINFO-MIB::atPortInfoTransceiverType'] ?? null,
                    'entity_physical_index' => $ifIndex,
                ]);
            });
    }
}
