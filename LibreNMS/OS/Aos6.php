<?php

/**
 * Aos6.php
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
use App\Models\PortVlan;
use App\Models\Vlan;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\BasicVlanDiscovery;
use LibreNMS\Interfaces\Discovery\PortVlanDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Aos6 extends OS implements BasicVlanDiscovery, PortVlanDiscovery
{
    public function discoverBasicVlanData(): Collection
    {
        return SnmpQuery::walk('ALCATEL-IND1-VLAN-MGR-MIB::vlanDescription')
            ->mapTable(function ($vlans, $vlan_id) {
                return new Vlan([
                    'vlan_vlan' => $vlan_id,
                    'vlan_name' => $vlans['ALCATEL-IND1-VLAN-MGR-MIB::vlanDescription'] ?? null,
                    'vlan_domain' => 1,
                    'vlan_type' => null,
                ]);
            });
    }

    public function discoverPortVlanData(Collection $vlans): Collection
    {
        return SnmpQuery::walk('ALCATEL-IND1-VLAN-MGR-MIB::vpaType')
            ->mapTable(function ($data, $vpaVlanNumber, $vpaIfIndex = null) {
                return new Portvlan([
                    'vlan' => $vpaVlanNumber,
                    'baseport' => $this->bridgePortFromIfIndex($vpaIfIndex),
                    'untagged' => ($data['ALCATEL-IND1-VLAN-MGR-MIB::vpaType'] == 1 ? 1 : 0),
                    'port_id' => PortCache::getIdFromIfIndex($vpaIfIndex, $this->getDeviceId()) ?? 0, // ifIndex from device
                ]);
            });
    }
}
