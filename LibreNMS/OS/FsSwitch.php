<?php
/**
 * Fs-switch.php
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
 * @copyright  2019 PipoCanaja
 * @author     PipoCanaja <pipocanaja@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class FsSwitch extends OS implements ProcessorDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $processors = [];

        // Tests OID from SWITCH MIB.
        $processors_data = snmpwalk_cache_oid($this->getDeviceArray(), 'ssCpuIdle', [], 'SWITCH', 'fs');

        foreach ($processors_data as $index => $entry) {
            $processors[] = Processor::discover(
                'fs-SWITCHMIB',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.27975.1.2.11.' . $index,
                $index,
                'CPU',
                -1,
                100 - $entry['ssCpuIdle']
            );
        }

        return $processors;
    }
}
