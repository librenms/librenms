<?php

/**
 * Edgeswitch.php
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
use App\Models\Ipv4Mac;
use App\Models\PortsFdb;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\ArpTableDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Polling\ProcessorPolling;
use LibreNMS\OS;
use LibreNMS\Util\Mac;

class Edgeswitch extends OS implements ProcessorDiscovery, ProcessorPolling, ArpTableDiscovery
{
    use Traits\VxworksProcessorUsage;

    public function discoverArpTable(): Collection
    {
        return \SnmpQuery::walk('EdgeSwitch-SWITCHING-MIB::agentDynamicDsBindingTable')
            ->mapTable(fn ($data) => new Ipv4Mac([
                'port_id' => (int) PortCache::getIdFromIfIndex($data['EdgeSwitch-SWITCHING-MIB::agentDynamicDsBindingIfIndex'], $this->getDevice()),
                'mac_address' => Mac::parse($data['EdgeSwitch-SWITCHING-MIB::agentDynamicDsBindingMacAddr'])->hex(),
                'ipv4_address' => $data['EdgeSwitch-SWITCHING-MIB::agentDynamicDsBindingIpAddr'],
            ]));
    }

    /**
     * The binding table only holds DHCP snooped clients, so it adds to the standard FDB, which wins when both have an entry.
     */
    public function discoverFdbTable(): Collection
    {
        return \SnmpQuery::walk('EdgeSwitch-SWITCHING-MIB::agentDynamicDsBindingTable')
            ->mapTable(fn ($data, $mac) => new PortsFdb([
                'port_id' => PortCache::getIdFromIfIndex($data['EdgeSwitch-SWITCHING-MIB::agentDynamicDsBindingIfIndex'] ?? null, $this->getDeviceId()) ?? 0,
                'mac_address' => Mac::parse($mac)->hex(),
                'vlan' => (int) ($data['EdgeSwitch-SWITCHING-MIB::agentDynamicDsBindingVlanId'] ?? 0) ?: null,
            ]))
            ->merge(parent::discoverFdbTable());
    }
}
