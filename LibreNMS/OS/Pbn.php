<?php

/**
 * Pbn.php
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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Link;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\LinkDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Pbn extends OS implements ProcessorDiscovery, LinkDiscovery
{
    public function __construct(&$device)
    {
        parent::__construct($device);

        if (preg_match('/^.* Build (?<build>\d+)/', (string) $this->getDevice()->version, $version)) {
            if ($version['build'] <= 16607) { // Buggy version :-(
                $this->stpTimeFactor = 1;
            }
        }
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors(): array
    {
        return [
            Processor::discover(
                'pbn-cpu',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.11606.10.9.109.1.1.1.1.5.1', // NMS-PROCESS-MIB::nmspmCPUTotal5min
                0
            ),
        ];
    }

    public function discoverLinks(): Collection
    {
        $links = new Collection;

        Log::info('NMS-LLDP-MIB:');
        $lldp_array = SnmpQuery::hideMib()->walk('NMS-LLDP-MIB::lldpRemoteSystemsData')->table(2);
        foreach ($lldp_array as $lldp_array_inner) {
            foreach ($lldp_array_inner as $lldp) {
                $interface = PortCache::getByIfIndex($lldp['lldpRemLocalPortNum'] ?? null, $this->getDeviceId());
                $remote_device_id = find_device_id($lldp['lldpRemSysName'] ?? null);
                if ($interface['port_id'] && $lldp['lldpRemSysName'] && $lldp['lldpRemPortId']) {
                    $remote_port_id = find_port_id($lldp['lldpRemPortDesc'], $lldp['lldpRemPortId'], $remote_device_id);
                    $links->push(new Link([
                        'local_port_id' => $interface['port_id'],
                        'remote_hostname' => $lldp['lldpRemSysName'],
                        'remote_device_id' => $remote_device_id,
                        'remote_port_id' => $remote_port_id,
                        'active' => 1,
                        'protocol' => 'lldp',
                        'remote_port' => $lldp['lldpRemPortId'] ?? '',
                        'remote_platform' => null,
                        'remote_version' => $lldp['lldpRemSysDesc'] ?? '',
                    ]));
                }
            }
        }

        return $links->filter();
    }
}
