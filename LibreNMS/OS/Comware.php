<?php
/**
 * Comware.php
 *
 * H3C/HPE Comware OS
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

use App\Models\Device;
use App\Models\Mempool;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;

class Comware extends OS implements MempoolsDiscovery, ProcessorDiscovery, TransceiverDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        // serial
        $serial_nums = explode("\n", snmp_walk($this->getDeviceArray(), 'hh3cEntityExtManuSerialNum', '-Osqv', 'HH3C-ENTITY-EXT-MIB'));
        $this->getDevice()->serial = $serial_nums[0]; // use the first s/n
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $processors = [];
        $procdata = snmpwalk_group($this->getDeviceArray(), 'hh3cEntityExtCpuUsage', 'HH3C-ENTITY-EXT-MIB');

        if (empty($procdata)) {
            return $processors;
        }
        $entity_data = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');

        foreach ($procdata as $index => $usage) {
            if ($usage['hh3cEntityExtCpuUsage'] != 0) {
                $processors[] = Processor::discover(
                    $this->getName(),
                    $this->getDeviceId(),
                    ".1.3.6.1.4.1.25506.2.6.1.1.1.1.6.$index",
                    $index,
                    $entity_data[$index],
                    1,
                    $usage['hh3cEntityExtCpuUsage'],
                    null,
                    $index
                );
            }
        }

        return $processors;
    }

    public function discoverMempools()
    {
        $mempools = new Collection();
        $data = snmpwalk_group($this->getDeviceArray(), 'hh3cEntityExtMemUsage', 'HH3C-ENTITY-EXT-MIB');

        if (empty($data)) {
            return $mempools; // avoid additional walks
        }

        $data = snmpwalk_group($this->getDeviceArray(), 'hh3cEntityExtMemSize', 'HH3C-ENTITY-EXT-MIB', 1, $data);
        $entity_name = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
        $entity_class = $this->getCacheByIndex('entPhysicalClass', 'ENTITY-MIB');

        foreach ($data as $index => $entry) {
            if ($entity_class[$index] == 'module' && $entry['hh3cEntityExtMemUsage'] > 0) {
                $mempools->push((new Mempool([
                    'mempool_index' => $index,
                    'mempool_type' => 'comware',
                    'mempool_class' => 'system',
                    'mempool_descr' => $entity_name[$index],
                    'mempool_precision' => 1,
                    'mempool_perc_oid' => ".1.3.6.1.4.1.25506.2.6.1.1.1.1.8.$index",
                ]))->fillUsage(null, $entry['hh3cEntityExtMemSize'] ?? null, null, $entry['hh3cEntityExtMemUsage'] ?? null));
            }
        }

        return $mempools;
    }

    public function discoverTransceivers(): Collection
    {
        $ifIndexToPortId = $this->getDevice()->ports()->pluck('port_id', 'ifIndex');

        return \SnmpQuery::cache()->enumStrings()->walk('HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverInfoTable')->mapTable(function ($data, $ifIndex) use ($ifIndexToPortId) {
            return new Transceiver([
                'port_id' => $ifIndexToPortId->get($ifIndex, 0),
                'index' => $ifIndex,
                'type' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverType'] ?? null,
                'vendor' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverVendorName'] ?? null,
                'oui' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverVendorOUI'] ?? null,
                'revision' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverRevisionNumber'] ?? null,
                'model' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverPartNumber'] ?? null,
                'serial' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverSerialNumber'] ?? null,
                'ddm' => isset($data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverDiagnostic']) && $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverDiagnostic'] == 'true',
                'cable' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverHardwareType'] ?? null,
                'distance' => $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverTransferDistance'] ?? null,
                'wavelength' => isset($data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverWaveLength']) && $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverWaveLength'] != 2147483647 ? $data['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverWaveLength'] : null,
                'entity_physical_index' => $ifIndex,
            ]);
        });
    }
}
