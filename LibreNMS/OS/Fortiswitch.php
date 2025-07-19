<?php

/**
 * Fortiswitch.php
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
use App\Models\PortsFdb;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\FdbTableDiscovery;
use LibreNMS\OS;

class Fortiswitch extends OS implements FdbTableDiscovery
{
    public function discoverFdbTable(): Collection
    {
        $fdbt = new Collection;

        $macTable = $this->dot1dTpFdbAddress();
        $portTable = $this->dot1dTpFdbPort();
        $basePortTable = $this->dot1dBasePort();

        foreach ($macTable as $dot1dTpFdbPort => $mac_address) {
            $fdbPort = $portTable[$dot1dTpFdbPort];
            $dot1dBasePort = array_search($fdbPort, $basePortTable);
            $ifIndex = $this->ifIndexFromBridgePort($dot1dBasePort);

            $vlan_id = 0; // Bug 9239914
            $fdbt->push(new PortsFdb([
                'port_id' => PortCache::getIdFromIfIndex($ifIndex) ?? 0,
                'mac_address' => $mac_address,
                'vlan_id' => $vlan_id,
            ]));
        }

        return $fdbt;
    }
}
