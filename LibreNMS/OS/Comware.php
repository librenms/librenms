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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Comware extends OS implements ProcessorDiscovery
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
        $procdata = $this->getCacheByIndex('hh3cEntityExtCpuUsage', 'HH3C-ENTITY-EXT-MIB');

        if (! empty($procdata)) {
            $entity_data = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
        }

        $processors = [];

        foreach ($procdata as $index => $usage) {
            if ($usage != 0) {
                $processors[] = Processor::discover(
                    $this->getName(),
                    $this->getDeviceId(),
                    ".1.3.6.1.4.1.25506.2.6.1.1.1.1.6.$index",
                    $index,
                    $entity_data[$index],
                    1,
                    $usage,
                    null,
                    $index
                );
            }
        }

        return $processors;
    }
}
