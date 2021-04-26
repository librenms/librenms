<?php
/**
 * Aos.php
 *
 * Alcatel-Lucent AOS
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Aos extends OS implements ProcessorDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $processor = Processor::discover(
            'aos-system',
            $this->getDeviceId(),
            '.1.3.6.1.4.1.6486.800.1.2.1.16.1.1.1.13.0', // ALCATEL-IND1-HEALTH-MIB::healthDeviceCpuLatest
            0,
            'Device CPU'
        );

        if (! $processor->isValid()) {
            // AOS7 devices use a different OID for CPU load. Not all Switches have
            // healthModuleCpuLatest so we use healthModuleCpu1MinAvg which makes no
            // difference for a 5 min. polling interval.
            // Note: This OID shows (a) the CPU load of a single switch or (b) the
            // average CPU load of all CPUs in a stack of switches.
            $processor = Processor::discover(
                'aos-system',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.6486.801.1.2.1.16.1.1.1.1.1.11.0',
                0,
                'Device CPU'
            );
        }

        return [$processor];
    }
}
