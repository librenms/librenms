<?php
/**
 * Boss.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Boss extends OS implements ProcessorDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $data = snmpwalk_group($this->getDevice(), 's5ChasUtilCPUUsageLast10Minutes', 'S5-CHASSIS-MIB');

        $processors = array();
        $count = 1;
        foreach ($data as $index => $entry) {
            $processors[] = Processor::discover(
                "avaya-ers",
                $this->getDeviceId(),
                ".1.3.6.1.4.1.45.1.6.3.8.1.1.6.$index",
                zeropad($count),
                "Unit $count processor",
                1,
                $entry['sgProxyCpuCoreBusyPerCent']
            );

            $count++;
        }


        return $processors;
    }
}
