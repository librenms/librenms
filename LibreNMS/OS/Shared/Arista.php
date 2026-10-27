<?php

/**
 * Arista.php
 *
 * Arista OS group
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

namespace LibreNMS\OS\Shared;

use App\Models\Vrf;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\BgpAddressFamilyDiscovery;
use LibreNMS\OS;
use LibreNMS\OS\Traits\Bgp4V2Mib;
use LibreNMS\Util\Oid;
use LibreNMS\Util\RouteDistinguisher;
use SnmpQuery;

class Arista extends OS implements BgpAddressFamilyDiscovery
{
    use Bgp4V2Mib;

    public function discoverBgpPeers(): Collection
    {
        return $this->discoverBgp4V2Peers('ARISTA-BGP4V2-MIB', 'aristaBgp4V2');
    }

    public function discoverBgpAddressFamilies(Collection $peers): Collection
    {
        return $this->discoverBgp4V2AddressFamilies($peers, 'ARISTA-BGP4V2-MIB', 'aristaBgp4V2');
    }

    public function discoverBgpLocalAs(): ?int
    {
        return $this->bgpLocalAsFallback(parent::discoverBgpLocalAs(), 'ARISTA-BGP4V2-MIB::aristaBgp4V2PeerLocalAs');
    }

    /**
     * ARISTA-VRF-MIB is indexed by the length prefixed VRF name
     */
    public function discoverVrfs(): Collection
    {
        $rds = SnmpQuery::hideMib()->numericIndex()->walk('ARISTA-VRF-MIB::aristaVrfRouteDistinguisher')->throwIfConnectionError()->pluck('aristaVrfRouteDistinguisher');

        return collect($rds)->map(fn ($rd, $index) => new Vrf([
            'vrf_oid' => (string) $index,
            'vrf_name' => Oid::stringFromOid((string) $index),
            'mplsVpnVrfRouteDistinguisher' => RouteDistinguisher::parse($rd),
            'mplsVpnVrfDescription' => '',
        ]))->values();
    }

    public function discoverVrfPorts(Collection $vrfs): Collection
    {
        $vrfOids = $vrfs->pluck('vrf_oid', 'vrf_name');
        $members = array_map(fn ($name) => $vrfOids->get($name, ''), SnmpQuery::hideMib()->walk('ARISTA-VRF-MIB::aristaVrfIfMembership')->throwIfConnectionError()->pluck('aristaVrfIfMembership'));

        return $this->vrfMemberPorts($members, $vrfs);
    }
}
