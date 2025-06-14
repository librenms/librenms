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
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Device;
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
        $ret = new Collection;

        $vlans = SnmpQuery::cache()->mibDir('nokia')->hideMib()->walk('ALCATEL-IND1-VLAN-MGR-MIB::vlanDescription')->table();
        foreach ($vlans['vlanDescription'] ?? [] as $vlan_id => $vlan_name) {
            $ret->push(new Vlan([
                'vlan_vlan' => $vlan_id,
                'vlan_name' => $vlan_name,
                'vlan_domain' => 1,
                'vlan_type' => null,
            ]));
        }

        return $ret;
    }

    public function discoverPortVlanData(): Collection
    {
        $ret = new Collection;

        $dot1dBasePortIfIndex = SnmpQuery::cache()->walk('BRIDGE-MIB::dot1dBasePortIfIndex')->table();
        $dot1dBasePortIfIndex = $dot1dBasePortIfIndex['BRIDGE-MIB::dot1dBasePortIfIndex'] ?? [];
        $index2base = array_flip($dot1dBasePortIfIndex);

        $vlanstype = SnmpQuery::cache()->mibDir('nokia')->hideMib()->walk('ALCATEL-IND1-VLAN-MGR-MIB::vpaType')->table();
        foreach ($vlanstype['vpaType'] ?? [] as $vlan_id => $data) {
            foreach ($data as $ifIndex => $porttype) {
                $baseport = $index2base[$ifIndex] ?? 0;
                $ret->push(new Portvlan([
                    'vlan' => $vlan_id,
                    'baseport' => $baseport,
                    'untagged' => ($porttype == 1 ? 1 : 0),
                    'port_id' => PortCache::getIdFromIfIndex($dot1dBasePortIfIndex[$baseport] ?? 0, $this->getDeviceId()) ?? 0, // ifIndex from device
                ]));
            }
        }

        return $ret;
    }
}
