<?php
/**
 * Foundry.php
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

namespace LibreNMS\OS\Shared;

use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Foundry extends OS implements ProcessorDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $processors_data = snmpwalk_cache_triple_oid($this->getDeviceArray(), 'snAgentCpuUtilTable', [], 'FOUNDRY-SN-AGENT-MIB');
        $module_descriptions = $this->getCacheByIndex('snAgentConfigModuleDescription', 'FOUNDRY-SN-AGENT-MIB');

        $processors = [];
        foreach ($processors_data as $index => $entry) {
            // use the 5 minute readings
            if ($entry['snAgentCpuUtilInterval'] != 300) {
                continue;
            }

            if (is_numeric($entry['snAgentCpuUtil100thPercent'])) {
                $usage_oid = '.1.3.6.1.4.1.1991.1.1.2.11.1.1.6.' . $index;
                $precision = 100;
                $usage = $entry['snAgentCpuUtil100thPercent'] / $precision;
            } elseif (is_numeric($entry['snAgentCpuUtilValue'])) {
                $usage_oid = '.1.3.6.1.4.1.1991.1.1.2.11.1.1.4.' . $index;
                $precision = 1;
                $usage = $entry['snAgentCpuUtilValue'] / $precision;
            } else {
                continue;
            }

            $module_description = $module_descriptions[$entry['snAgentCpuUtilSlotNum']];
            [$module_description] = explode(' ', $module_description);
            $descr = "Slot {$entry['snAgentCpuUtilSlotNum']} $module_description [{$entry['snAgentCpuUtilSlotNum']}]";

            $processors[] = Processor::discover(
                $this->getName(),
                $this->getDeviceId(),
                $usage_oid,
                $index,
                $descr,
                $precision,
                $usage
            );
        }

        return $processors;
    }
}
