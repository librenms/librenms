<?php
/*
 * Viptela.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Polling\ProcessorPolling;
use LibreNMS\OS;

class Viptela extends OS implements ProcessorDiscovery, ProcessorPolling
{
    private string $procOid = '.1.3.6.1.4.1.41916.11.1.16.0';

    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        $device->hardware = $this->getHardware($device->hardware);
    }

    private function getHardware($id)
    {
        $hardware = [
            '1' => 'Viptela vSmart Controller',
            '2' => 'Viptela vManage NMS',
            '3' => 'Viptela vBond Orchestrator',
            '4' => 'Viptela vEdge-1000',
            '5' => 'Viptela vEdge-2000',
            '6' => 'Viptela vEdge-100',
            '7' => 'Viptela vEdge-100-W2',
            '8' => 'Viptela vEdge-100-WM',
            '9' => 'Viptela vEdge-100-M2',
            '10' => 'Viptela vEdge-100-M',
            '11' => 'Viptela vEdge-100-B',
            '12' => 'Viptela vEdge Cloud',
            '13' => 'Viptela vContainer',
            '14' => 'Viptela vEdge-5000',
        ];

        return $hardware[(string) $id] ?? $id;
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processor
     */
    public function discoverProcessors()
    {
        $idle_cpu = 100 - \SnmpQuery::get([$this->procOid])->value();
        $processors[] = Processor::discover(
            'viptela',
            $this->getDeviceId(),
            $this->procOid,
            0,
            'Processor',
            1,
            $idle_cpu,
        );

        return $processors;
    }

    /**
     * Poll processor data.  This can be implemented if custom polling is needed.
     *
     * @param  array  $processors  Array of processor entries from the database that need to be polled
     * @return array of polled data
     */
    public function pollProcessors(array $processors)
    {
        $data = [];

        foreach ($processors as $processor) {
            $data[$processor['processor_id']] = 100 - \SnmpQuery::get([$this->procOid])->value();
        }

        return $data;
    }
}
