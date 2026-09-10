<?php

/**
 * Swos.php
 *
 * Mikrotik SwitchOS
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
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\Number;
use SnmpQuery;

class Swos extends OS implements TransceiverDiscovery
{
    public function discoverTransceivers(): Collection
    {
        return SnmpQuery::walk('MIKROTIK-MIB::mtxrOpticalTable')->mapTable(function ($data, $ifIndex) {
            $wavelength = $data['MIKROTIK-MIB::mtxrOpticalWavelength'];
            $wavelength = isset($wavelength) && $wavelength != '.00' && $wavelength != '42949671.68' ? Number::cast($wavelength) : null;

            // don't create an entry when there's nothing to display - the SNMP table contains all (also empty) slots
            if ($wavelength == null && $data['MIKROTIK-MIB::mtxrOpticalTxBiasCurrent'] == '0' &&
                $data['MIKROTIK-MIB::mtxrOpticalSupplyVoltage'] == '.000' && $data['MIKROTIK-MIB::mtxrOpticalTemperature'] == '4294967168') {
                return null;
            }

            return new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $ifIndex,
                'vendor' => $data['MIKROTIK-MIB::mtxrOpticalVendorName'] ?? null,
                'serial' => $data['MIKROTIK-MIB::mtxrOpticalVendorSerial'] ?? null,
                'wavelength' => $wavelength,
                'entity_physical_index' => $ifIndex,
            ]);
        })->filter();
    }
}
