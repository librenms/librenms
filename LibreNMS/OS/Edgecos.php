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

namespace LibreNMS\OS;

use App\Models\Processor;
use App\Models\Mempool;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Edgecos extends OS implements ProcessorDiscovery, MempoolsDiscovery, TransceiverDiscovery
{
    public function discoverProcessors(): array
    {
        $processors = [];
        $data = SnmpQuery::walk(['ECS4120-MIB::deviceCpuUsage'])->table(1);

        foreach ($data as $index => $entry) {
            $processors[] = new Processor([
                'device_id' => $this->getDeviceId(),
                'index' => $index,
                'usage_oid' => "ECS4120-MIB::deviceCpuUsage.$index",
                'description' => "CPU $index",
                'precision' => 1,
                'usage' => $entry['ECS4120-MIB::deviceCpuUsage'] ?? 0,
            ]);
        }

        return $processors;
    }

    public function discoverMempools(): Collection
    {
        $mempools = new Collection();
        $data = SnmpQuery::walk(['ECS4120-MIB::deviceMemoryUsage', 'ECS4120-MIB::deviceMemorySize'])->table(1);

        foreach ($data as $index => $entry) {
            if (isset($entry['ECS4120-MIB::deviceMemorySize']) && $entry['ECS4120-MIB::deviceMemorySize'] > 0) {
                $mempools->push(new Mempool([
                    'mempool_index' => $index,
                    'device_id' => $this->getDeviceId(),
                    'mempool_type' => 'edgecos',
                    'mempool_class' => 'system',
                    'precision' => 1,
                    'usage_oid' => "ECS4120-MIB::deviceMemoryUsage.$index",
                    'size_oid' => "ECS4120-MIB::deviceMemorySize.$index",
                    'mempool_descr' => "Memory $index",
                    'usage' => $entry['ECS4120-MIB::deviceMemoryUsage'] ?? 0,
                    'size' => $entry['ECS4120-MIB::deviceMemorySize'] ?? 0,
                ]));
            }
        }

        return $mempools;
    }

    public function discoverTransceivers(): Collection
    {
        $ifIndexToPortId = $this->getDevice()->ports()->pluck('port_id', 'ifIndex');

        return SnmpQuery::walk('ECS4120-MIB::portMediaInfoTable')->mapTable(function ($data, $index) use ($ifIndexToPortId) {
            if ($data['ECS4120-MIB::portMediaInfoConnectorType'] === 'inactive') {
                return null;
            }

            return new Transceiver([
                'port_id' => $ifIndexToPortId->get($index),
                'index' => $index,
                'vendor' => $data['ECS4120-MIB::portMediaInfoVendorName'] ?? null,
                'model' => $data['ECS4120-MIB::portMediaInfoPartNumber'] ?? null,
                'serial' => $data['ECS4120-MIB::portMediaInfoSerialNumber'] ?? null,
                'type' => $data['ECS4120-MIB::portMediaInfoConnectorType'] ?? null,
                'wavelength' => $data['ECS4120-MIB::portMediaInfoEthComplianceCodes'] ?? null,
                'cable' => $data['ECS4120-MIB::portMediaInfoFiberType'] ?? null,
                'distance' => $data['ECS4120-MIB::portMediaInfoBaudRate'] ?? null,
            ]);
        })->filter();
    }
}
