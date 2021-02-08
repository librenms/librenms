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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Boss extends OS implements OSDiscovery, ProcessorDiscovery
{
    public function discoverOS(Device $device): void
    {
        // Try multiple ways of getting firmware version
        $version = null;
        preg_match('/SW:v?([^ ]+) /', $device->sysDescr, $version_matches);
        $version = $version_matches[1] ?? null;

        if (empty($version)) {
            $version = explode(' on', snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.2272.1.1.7.0', '-Oqvn'))[0] ?? null;
        }
        if (empty($version)) {
            $version = snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.45.1.6.4.2.1.10.0', '-Oqvn') ?? null;
        }
        $device->version = $version;

        // Get hardware details, expand ERS to normalize
        $details = str_replace('ERS', 'Ethernet Routing Switch', $device->sysDescr);

        // Make boss devices hardware string compact
        $details = str_replace('Ethernet Routing Switch ', 'ERS-', $details);
        $details = str_replace('Virtual Services Platform ', 'VSP-', $details);
        $device->hardware = explode(' ', $details, 2)[0] ?? null;

        // Is this a 5500 series or 5600 series stack?
        $stack = snmp_walk($this->getDeviceArray(), '.1.3.6.1.4.1.45.1.6.3.3.1.1.6.8', '-OsqnU');
        $stack = explode("\n", $stack);
        $stack_size = count($stack);
        if ($stack_size > 1) {
            $device->features = "Stack of $stack_size units";
        }
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $data = snmpwalk_group($this->getDeviceArray(), 's5ChasUtilCPUUsageLast10Minutes', 'S5-CHASSIS-MIB');

        $processors = [];
        $count = 1;
        foreach ($data as $index => $entry) {
            $processors[] = Processor::discover(
                'avaya-ers',
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
