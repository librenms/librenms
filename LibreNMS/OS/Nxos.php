<?php

/**
 * Nxos.php
 *
 * Cisco NX-OS
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Vrf;
use Illuminate\Support\Collection;
use SnmpQuery;

class Nxos extends Shared\Cisco
{
    /**
     * NX-OS only fills the VRF MIBs when MPLS L3VPN is configured,
     * so VRFs with BGP sessions are taken from cbgpPeer3Table
     */
    public function discoverVrfs(): Collection
    {
        $vrfs = parent::discoverVrfs();
        if ($vrfs->isNotEmpty()) {
            return $vrfs;
        }

        $names = [];
        foreach (SnmpQuery::hideMib()->numericIndex()->walk('CISCO-BGP4-MIB::cbgpPeer3VrfName')->throwIfConnectionError()->pluck('cbgpPeer3VrfName') as $index => $name) {
            $names[explode('.', (string) $index)[0]] = (string) $name; // cbgpPeer3VrfId, type, address
        }

        return collect($names)
            ->reject(fn (string $name) => $name === '' || Vrf::isDefaultName($name))
            ->map(fn (string $name, $vrfId) => new Vrf(['vrf_oid' => (string) $vrfId, 'vrf_name' => $name, 'mplsVpnVrfDescription' => '']))
            ->values();
    }
}
