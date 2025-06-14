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
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Discovery\VlanDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Aos6 extends OS implements VlanDiscovery
{
    public function discoverVlans($dot1dBasePortIfIndex): array
    {
        $vlanData = [];

        $index2base = array_flip($dot1dBasePortIfIndex);

        $vlans = SnmpQuery::mibDir('nokia')->hideMib()->walk('ALCATEL-IND1-VLAN-MGR-MIB::vlanDescription')->table();

        foreach ($vlans['vlanDescription'] ?? [] as $vlan_id => $vlan_name) {
            $vlanData['basic'][] = [
                'vlan_vlan' => $vlan_id,
                'vlan_name' => $vlan_name,
                'vlan_domain' => 1,
                'vlan_type' => null,
            ];
        }
        $vlanstype = $vlans = SnmpQuery::mibDir('nokia')->hideMib()->walk('ALCATEL-IND1-VLAN-MGR-MIB::vpaType')->table();
        foreach ($vlanstype['vpaType'] ?? [] as $vlan_id => $data) {
            foreach ($data as $ifIndex => $porttype) {
                $vlanData['ports'][] = [
                    'vlan' => $vlan_id,
                    'baseport' => $index2base[$ifIndex] ?? 0,
                    'untagged' => ($porttype == 1 ? 1 : 0),
                    'ifIndex' => $ifIndex,
                ];
            }
        }

        return $vlanData;
    }
}
