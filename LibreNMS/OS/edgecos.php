<?php
/**
 * Edgecos.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

<?php

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Transceiver;
use App\Models\Processor;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Edgecos extends OS implements TransceiverDiscovery, MempoolsDiscovery, ProcessorDiscovery
{
    public function discoverTransceivers(): Collection
    {
        $ifIndexToPortId = $this->getDevice()->ports()->pluck('port_id', 'ifIndex');
        $entityToIfIndex = $this->getIfIndexEntPhysicalMap();

        return SnmpQuery::walk('ECS4120-MIB::portMediaInfoTable')->mapTable(function ($data, $entIndex) use ($entityToIfIndex, $ifIndexToPortId) {
            // Skip inactive transceivers
            if ($data['ECS4120-MIB::portMediaInfoConnectorType'] === 'inactive') {
                return null;
            }

            $distance = $data['ECS4120-MIB::portMediaInfoBaudRate'] ?? null;
            $cable = $data['ECS4120-MIB::portMediaInfoFiberType'] ?? null;

            $ifIndex = $entityToIfIndex[$entIndex] ?? null;
            $port_id = $ifIndexToPortId->get($ifIndex);

            if (is_null($port_id)) {
                return null;
            }

            return new Transceiver([
                'port_id' => $port_id,
                'index' => $entIndex,
                'vendor' => $data['ECS4120-MIB::portMediaInfoVendorName'] ?? null,
                'type' => $data['ECS4120-MIB::portMediaInfoConnectorType'] ?? null,
                'model' => $data['ECS4120-MIB::portMediaInfoPartNumber'] ?? null,
                'serial' => $data['ECS4120-MIB::portMediaInfoSerialNumber'] ?? null,
                'cable' => $cable,
                'distance' => $distance,
                'wavelength' => $data['ECS4120-MIB::portMediaInfoEthComplianceCodes'] ?? null,
                'entity_physical_index' => $entIndex,
            ]);
        })->filter();
    }

    public function discoverEntityPhysical(): Collection
    {
        $inventory = parent::discoverEntityPhysical();

        $extra = SnmpQuery::walk([
            'ECS4120-MIB::portMediaInfoVendorName',
            'ECS4120-MIB::portMediaInfoPartNumber',
        ])->table(1);

        $inventory->each(function ($entry) use ($extra) {
            if (isset($entry->entPhysicalIndex)) {
                $entry->entPhysicalDescr = $extra[$entry->entPhysicalIndex]['ECS4120-MIB::portMediaInfoVendorName'] ?? '';
                $entry->entPhysicalModelName = $extra[$entry->entPhysicalIndex]['ECS4120-MIB::portMediaInfoPartNumber'] ?? '';
            }
        });

        return $inventory;
    }

    public function discoverProcessors(): array
    {
        $device = $this->getDeviceArray();

        $processors = [];
        $data = SnmpQuery::walk('ECS4120-MIB::deviceCpuUsageTable')->table(1);

        foreach ($data as $index => $entry) {
            $usage_oid = ".1.3.6.1.4.1.259.10.1.2.1.5." . $index;
            $descr = $entry['ECS4120-MIB::deviceCpuUsageDescr'] ?? "Processor $index";
            $usage = $entry['ECS4120-MIB::deviceCpuUsage'] ?? 0;

            $processors[] = new Processor([
                'device_id' => $this->getDeviceId(),
                'index' => $index,
                'usage_oid' => $usage_oid,
                'description' => $descr,
                'precision' => 1,
                'usage' => $usage,
            ]);
        }

        return $processors;
    }

    public function discoverMempools(): Collection
    {
        $device = $this->getDeviceArray();

        $mempools = new Collection();
        $data = SnmpQuery::walk([
            'ECS4120-MIB::deviceMemoryUsage',
            'ECS4120-MIB::deviceMemorySize',
        ])->table(1);

        foreach ($data as $index => $entry) {
            $size = $entry['ECS4120-MIB::deviceMemorySize'] ?? 0;
            $usage = $entry['ECS4120-MIB::deviceMemoryUsage'] ?? 0;
            $descr = "Memory $index";

            if ($size > 0) {
                $mempools->push((new Mempool([
                    'mempool_index' => $index,
                    'mempool_type' => 'edgecos',
                    'mempool_class' => 'system',
                    'mempool_precision' => 1,
                    'mempool_descr' => $descr,
                ]))->fillUsage($usage, $size));
            }
        }

        return $mempools;
    }
}

