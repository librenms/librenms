<?php
/**
 * VxworksProcessorUsage.php
 *
 * Several devices use the janky output of this oid
 * Requires both ProcessorDiscovery and ProcessorPolling
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

namespace LibreNMS\OS\Traits;

use LibreNMS\Device\Processor;

trait VxworksProcessorUsage
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @param string $oid Custom OID to fetch from
     * @return array Processors
     */
    public function discoverProcessors($oid = '.1.3.6.1.4.1.4413.1.1.1.1.4.9.0')
    {
        $usage = $this->parseCpuUsage(snmp_get($this->getDeviceArray(), $oid, '-Ovq'));
        if (is_numeric($usage)) {
            return [
                Processor::discover(
                    $this->getName(),
                    $this->getDeviceId(),
                    $oid,
                    0,
                    'Processor',
                    1,
                    $usage
                ),
            ];
        }

        return [];
    }

    /**
     * Poll processor data.  This can be implemented if custom polling is needed.
     *
     * @param array $processors Array of processor entries from the database that need to be polled
     * @return array of polled data
     */
    public function pollProcessors(array $processors)
    {
        $data = [];

        foreach ($processors as $processor) {
            $data[$processor['processor_id']] = $this->parseCpuUsage(
                snmp_get($this->getDeviceArray(), $processor['processor_oid'])
            );
        }

        return $data;
    }

    /**
     * Parse the silly cpu usage string
     * "    5 Secs ( 96.4918%)   60 Secs ( 54.2271%)  300 Secs ( 38.2591%)"
     *
     * @param string $data
     * @return mixed
     */
    private function parseCpuUsage($data)
    {
        preg_match('/([0-9]+.[0-9]+)%/', $data, $matches);

        return $matches[1];
    }
}
