<?php

/**
 * Smartbyte.php
 *
 * SmartByte OS
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
 * @copyright  2025 Frederik Kriewitz
 * @author     Frederik Kriewitz <frederik@kriewitz.eu>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Smartbyte extends OS implements TransceiverDiscovery
{
    public function discoverTransceivers(): Collection
    {
        return SnmpQuery::cache()->walk('NMS-OPTICAL-MIB::opticalModuleInfoTable')->mapTable(function ($data, $index) {
            $ifIndex = $data['NMS-OPTICAL-MIB::opticalPortIndex'];

            if (empty($data['NMS-OPTICAL-MIB::opticalTransceiverType'])) {
                return null;
            }

            $ddm = $data['NMS-OPTICAL-MIB::opticalSupportDDM'] ? boolval($data['NMS-OPTICAL-MIB::opticalSupportDDM']) : null;
            $distance = $data['NMS-OPTICAL-MIB::opticalTransferDistance'] ? $data['NMS-OPTICAL-MIB::opticalTransferDistance'] * 1000 : null;

            return new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $ifIndex,
                'entity_physical_index' => $ifIndex,
                'type' => $data['NMS-OPTICAL-MIB::opticalTransceiverType'] ?? null,
                'vendor' => $data['NMS-OPTICAL-MIB::opticalVendorName'] ?? null,
                'model' => $data['NMS-OPTICAL-MIB::opticalPartNumber'] ?? null,
                'serial' => $data['NMS-OPTICAL-MIB::opticalSerialNumber'] ?? null,
                'ddm' => $ddm,
                'distance' => $distance,
                'wavelength' => $data['NMS-OPTICAL-MIB::opticalWaveLength'] ?? null,
                'connector' => $data['NMS-OPTICAL-MIB::opticalConnectType'] ?? null,
            ]);
        })->filter();
    }
}
