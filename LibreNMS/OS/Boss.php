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
 *
 * @copyright  2025 Peca Nesovanovic
 * @copyright  2025 Tony Murray
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\PortVlan;
use App\Models\Vlan;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\VlanDiscovery;
use LibreNMS\Interfaces\Discovery\VlanPortDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\StringHelpers;
use SnmpQuery;

class Boss extends OS implements OSDiscovery, ProcessorDiscovery, VlanDiscovery, VlanPortDiscovery
{
    public function discoverOS(Device $device): void
    {
        // Try multiple ways of getting firmware version
        $version = null;
        preg_match('/SW:v?([^ ]+) /', $device->sysDescr, $version_matches);
        $version = $version_matches[1] ?? null;

        if (empty($version)) {
            $version = explode(' on', snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.2272.1.1.7.0', '-Oqvn'))[0] ?: null;
        }
        if (empty($version)) {
            $version = snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.45.1.6.4.2.1.10.0', '-Oqvn') ?: null;
        }
        $device->version = $version;

        // Get hardware details, expand ERS to normalize
        $details = str_replace('ERS', 'Ethernet Routing Switch', $device->sysDescr);

        // Make boss devices hardware string compact
        $details = str_replace('Ethernet Routing Switch ', 'ERS-', $details);
        $details = str_replace('Virtual Services Platform ', 'VSP-', $details);
        $device->hardware = explode(' ', $details, 2)[0] ?: null;

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
                Str::padLeft((string) $count, 2, '0'),
                "Unit $count processor",
                1,
                $entry['sgProxyCpuCoreBusyPerCent'] ?? null
            );

            $count++;
        }

        return $processors;
    }

    public function discoverVlans(): Collection
    {
        if (($QBridgeMibVlans = parent::discoverVlans())->isNotEmpty()) {
            return $QBridgeMibVlans;
        }

        return SnmpQuery::walk('RC-VLAN-MIB::rcVlanName')
            ->mapTable(function ($vlan, $vlan_id) {
                return new Vlan([
                    'vlan_vlan' => $vlan_id,
                    'vlan_name' => $vlan['RC-VLAN-MIB::rcVlanName'] ?? '',
                    'vlan_domain' => 1,
                ]);
            });
    }

    public function discoverVlanPorts(Collection $vlans): Collection
    {
        $ports = parent::discoverVlanPorts($vlans); // Q-BRIDGE-MIB
        if ($ports->isNotEmpty()) {
            return $ports;
        }

        $egress_vlans = SnmpQuery::walk('RC-VLAN-MIB::rcVlanPortMembers')->pluck();

        if (empty($egress_vlans)) {
            return $ports;
        }

        // find default vlans (untagged or otherwise)
        $port_data = SnmpQuery::walk([
            'RC-VLAN-MIB::rcVlanPortDefaultVlanId',
            'RC-VLAN-MIB::rcVlanPortPerformTagging',
        ])->table(1);
        $pvid_untagged = [];
        foreach ($port_data as $rcVlanPortIndex => $data) {
            $vlan_id = $data['RC-VLAN-MIB::rcVlanPortDefaultVlanId'] ?? '';
            // 2 false, 4 untagPvidOnly from RC-VLAN-MIB
            $untagged = in_array($data['RC-VLAN-MIB::rcVlanPortPerformTagging'] ?? '', ['2', '4']) ? 1 : 0;

            $pvid_untagged[$vlan_id][$rcVlanPortIndex] = $untagged;
        }

        foreach ($vlans as $vlan) {
            $vlan_id = $vlan->vlan_vlan;
            if (empty($egress_vlans[$vlan_id])) {
                continue;
            }

            $egress_ids = StringHelpers::bitsToIndices($egress_vlans[$vlan_id]);

            foreach ($egress_ids as $baseport) {
                $ports->push(new PortVlan([
                    'vlan' => $vlan_id,
                    'baseport' => $baseport - 1, // why -1?
                    'untagged' => $pvid_untagged[$vlan_id][$baseport - 1] ?? 0,
                    'port_id' => PortCache::getIdFromIfIndex($this->ifIndexFromBridgePort($baseport - 1), $this->getDeviceId()) ?? 0, // ifIndex from device
                ]));
            }
        }

        return $ports;
    }
}
