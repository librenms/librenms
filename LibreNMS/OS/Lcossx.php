<?php
/**
 * Lcossx.php
 *
 * LANCOM LCOS SX
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
 * @copyright  2024 Rudy Broersma
 * @author     Rudy Broersma <tozz@kijkt.tv>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Polling\ProcessorPolling;
use LibreNMS\OS;

class Lcossx extends OS implements ProcessorDiscovery, ProcessorPolling
{
    private string $procOid = '1.3.6.1.4.1.2356.14.1.1.1.24.0';

    // OID string value example: 100ms:87%, 1s:49%, 10s:42%
    private function convertProcessorData(array $input)
    {
        $data = [];
        $cpuList = explode(',', reset($input)[0]);
        foreach ($cpuList as $cpuPart) {
            $cpuValues = explode(':', $cpuPart);
            $cpuName = trim($cpuValues[0]);
            $cpuPerc = str_replace('%', '', $cpuValues[1]);
            $data[$cpuName] = $cpuPerc;
        }

        return $data;
    }

    public function discoverProcessors()
    {
        $data = snmpwalk_array_num($this->getDeviceArray(), $this->procOid);
        if ($data === false) {
            return [];
        }

        $processors = [];
        $count = 0;
        foreach ($this->convertProcessorData($data) as $cpuName => $cpuPerc) {
            $processors[] = Processor::discover(
                'lcossx',
                $this->getDeviceId(),
                $this->procOid,
                $count,
                'Processor ' . $cpuName,
                1,
                $cpuPerc,
                100
            );
            $count++;
        }

        return $processors;
    }

    public function pollProcessors(array $processors)
    {
        $data = snmpwalk_array_num($this->getDeviceArray(), $this->procOid);
        if (get_debug_type($data) != 'array') {
            return [];
        }

        $cpuList = $this->convertProcessorData($data);

        $data = [];
        foreach ($processors as $processor) {
            $processor_id = $processor['processor_id'];
            $key = explode(' ', $processor['processor_descr'])[1];
            $value = $cpuList[$key];
            $data[$processor_id] = $value;
        }

        return $data;
    }
}
