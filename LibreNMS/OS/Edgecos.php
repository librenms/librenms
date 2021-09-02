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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Mempool;
use Illuminate\Support\Str;
use LibreNMS\Device\Processor;
use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Edgecos extends OS implements MempoolsDiscovery, ProcessorDiscovery
{
    public function discoverMempools()
    {
        $mib = $this->findMib();
        $data = snmp_get_multi_oid($this->getDeviceArray(), ['memoryTotal.0', 'memoryFreed.0', 'memoryAllocated.0'], '-OUQs', $mib);

        if (empty($data)) {
            return collect();
        }

        $mempool = new Mempool([
            'mempool_index' => 0,
            'mempool_type' => 'edgecos',
            'mempool_class' => 'system',
            'mempool_precision' => 1,
            'mempool_descr' => 'Memory',
            'mempool_perc_warn' => 90,
        ]);

        if ($data['memoryAllocated.0']) {
            $mempool->mempool_used_oid = YamlDiscovery::oidToNumeric('memoryAllocated.0', $this->getDeviceArray(), $mib);
        } else {
            $mempool->mempool_free_oid = YamlDiscovery::oidToNumeric('memoryFreed.0', $this->getDeviceArray(), $mib);
        }

        $mempool->fillUsage($data['memoryAllocated.0'], $data['memoryTotal.0'], $data['memoryFreed.0']);

        return collect([$mempool]);
    }

    public function discoverOS(Device $device): void
    {
        $mib = $this->findMib();
        $data = snmp_get_multi($this->getDeviceArray(), ['swOpCodeVer.1', 'swProdName.0', 'swSerialNumber.1', 'swHardwareVer.1'], '-OQUs', $mib);

        $device->version = trim($data[1]['swHardwareVer'] . ' ' . $data[1]['swOpCodeVer']) ?: null;
        $device->hardware = $data[0]['swProdName'] ?? null;
        $device->serial = $data[1]['swSerialNumber'] ?? null;
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $device = $this->getDevice();

        if (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.24.')) { //ECS4510
            $oid = '.1.3.6.1.4.1.259.10.1.24.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.22.')) { //ECS3528
            $oid = '.1.3.6.1.4.1.259.10.1.22.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.39.')) { //ECS4110
            $oid = '.1.3.6.1.4.1.259.10.1.39.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.45.')) { //ECS4120
            $oid = '.1.3.6.1.4.1.259.10.1.45.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.42.')) { //ECS4210
            $oid = '.1.3.6.1.4.1.259.10.1.42.101.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.27.')) { //ECS3510
            $oid = '.1.3.6.1.4.1.259.10.1.27.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.8.1.11.')) { //ES3510MA
            $oid = '.1.3.6.1.4.1.259.8.1.11.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.46.')) { //ECS4100-52T
            $oid = '.1.3.6.1.4.1.259.10.1.46.1.39.2.1.0';
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.259.10.1.5')) { //ECS4610-24F
            $oid = '.1.3.6.1.4.1.259.10.1.5.1.39.2.1.0';
        }

        if (isset($oid)) {
            return [
                Processor::discover(
                    $this->getName(),
                    $this->getDeviceId(),
                    $oid,
                    0
                ),
            ];
        }

        return [];
    }

    /**
     * Find the MIB based on sysObjectID
     * @return string
     */
    protected function findMib(): ?string
    {
        $table = [
            '.1.3.6.1.4.1.259.6.' => 'ES3528MO-MIB',
            '.1.3.6.1.4.1.259.10.1.22.' => 'ES3528MV2-MIB',
            '.1.3.6.1.4.1.259.10.1.24.' => 'ECS4510-MIB',
            '.1.3.6.1.4.1.259.10.1.39.' => 'ECS4110-MIB',
            '.1.3.6.1.4.1.259.10.1.42.' => 'ECS4210-MIB',
            '.1.3.6.1.4.1.259.10.1.27.' => 'ECS3510-MIB',
            '.1.3.6.1.4.1.259.10.1.45.' => 'ECS4120-MIB',
            '.1.3.6.1.4.1.259.8.1.11' => 'ES3510MA-MIB',
            '.1.3.6.1.4.1.259.10.1.43.' => 'ECS2100-MIB',
            '.1.3.6.1.4.1.259.10.1.46.' => 'ECS4100-52T-MIB',
            '.1.3.6.1.4.1.259.10.1.5' => 'ECS4610-24F-MIB',
        ];

        foreach ($table as $prefix => $mib) {
            if (Str::startsWith($this->getDevice()->sysObjectID, $prefix)) {
                return $mib;
            }
        }

        return null;
    }
}
