<?php
/**
 * DahuaNvr.php
 *
 * Dahua NVR
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
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class DahuaNvr extends OS implements ProcessorDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $device = $this->getDevice();

        $processors_data = snmpwalk_cache_multi_oid($device, 'cpuUsage', array(), 'DAHUA-SNMP-MIB');

        $processors = array();
        foreach ($processors_data as $index => $entry) {
            if (is_numeric($entry['cpuUsage'])) {
                d_echo("$index {$entry['cpuUsage']}\n");

                $usage_oid = '.1.3.6.1.4.1.1004849.2.1.3.'.$index;
                $descr = "Processor " . ($index + 1);
                $usage = $entry['cpuUsage'];

                $processors[] = Processor::discover(
                    $this->getName(),
                    $this->getDeviceId(),
                    $usage_oid,
                    $index,
                    $descr,
                    1,
                    $usage
                );
            }
        }

        return $processors;
    }
}
