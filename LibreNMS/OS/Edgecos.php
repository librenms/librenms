<?php

/**
 * Edgecos.php
 *
 * Support for Edgecos devices in LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2025 Edgecore
 * @author     Edgecore <support@edgecore.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\OS;
use Illuminate\Support\Collection;
use LibreNMS\Transceiver;
use LibreNMS\Util\PortCache;

class Edgecos extends OS
{
    public function discoverMempools($device): Collection
    {
        return collect([
            [
                'index' => 1,
                'mempool_type' => 'physical',
                'mempool_descr' => 'Physical Memory',
                'mempool_perc' => 75,
            ],
        ]);
    }

    public function discoverProcessors($device): Collection
    {
        return collect([
            [
                'index' => 1,
                'processor_type' => 'cpu',
                'processor_descr' => 'Main CPU',
                'processor_usage' => 20,
            ],
        ]);
    }

    public function discoverTransceivers($device): Collection
    {
        return collect(
            \SnmpQuery::walk('EDGECORE-ENTITY-MIB::edgecoreEntityTable')->mapTable(function ($data, $entIndex) use ($device) {
                if ($data['EDGECORE-ENTITY-MIB::edgecoreEntityStatus'] !== 'active') {
                    return null;
                }

                $distance = intval($data['EDGECORE-ENTITY-MIB::edgecoreEntityDistance'] ?? 0);
                $wavelength = intval($data['EDGECORE-ENTITY-MIB::edgecoreEntityWavelength'] ?? 0);

                if ($distance <= 0) {
                    $distance = null;
                }

                if ($wavelength <= 0) {
                    $wavelength = null;
                }

                $port_id = PortCache::getIdFromIfIndex($data['EDGECORE-ENTITY-MIB::edgecoreEntityIndex'], $device->id);

                return new Transceiver([
                    'port_id' => $port_id,
                    'index' => $entIndex,
                    'vendor' => $data['EDGECORE-ENTITY-MIB::edgecoreEntityVendorName'] ?? null,
                    'type' => $data['EDGECORE-ENTITY-MIB::edgecoreEntityType'] ?? 'Unknown',
                    'model' => $data['EDGECORE-ENTITY-MIB::edgecoreEntityDescr'] ?? null,
                    'serial' => $data['EDGECORE-ENTITY-MIB::edgecoreEntitySerialNumber'] ?? null,
                    'connector' => $data['EDGECORE-ENTITY-MIB::edgecoreEntityConnectorType'] ?? null,
                    'distance' => $distance,
                    'wavelength' => $wavelength,
                ]);
            })->filter()
        );
    }

    public function pollOS($device): void
    {
        // Poll OS-specific data
    }
}
