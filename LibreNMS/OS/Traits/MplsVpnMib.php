<?php

/**
 * MplsVpnMib.php
 *
 * VRF discovery from MPLS-L3VPN-STD-MIB and the earlier MPLS-VPN-MIB draft.
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

namespace LibreNMS\OS\Traits;

use App\Facades\PortCache;
use App\Models\Port;
use App\Models\Vrf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Util\Oid;
use LibreNMS\Util\RouteDistinguisher;
use SnmpQuery;

trait MplsVpnMib
{
    /**
     * Both MIBs index VRFs by their length prefixed name, which is used as vrf_oid
     *
     * @return Collection<int, Vrf>
     */
    public function discoverVrfs(): Collection
    {
        $vrfs = $this->discoverMplsVpnVrfs('MPLS-L3VPN-STD-MIB::mplsL3VpnVrfRD', 'MPLS-L3VPN-STD-MIB::mplsL3VpnVrfDescription');

        // devices implement an earlier MPLS-VPN-MIB draft than the MIB file, without mplsVpnVrfVpnId,
        // so the route distinguisher and description columns are named one off
        return $vrfs->isNotEmpty() ? $vrfs : $this->discoverMplsVpnVrfs('MPLS-VPN-MIB::mplsVpnVrfDescription', 'MPLS-VPN-MIB::mplsVpnVrfVpnId');
    }

    /**
     * @param  Collection<int, Vrf>  $vrfs
     * @return Collection<int, Port>
     */
    public function discoverVrfPorts(Collection $vrfs): Collection
    {
        if ($vrfs->isEmpty()) {
            return new Collection;
        }

        foreach (['MPLS-L3VPN-STD-MIB::mplsL3VpnIfVpnClassification', 'MPLS-VPN-MIB::mplsVpnIfLabelEdgeType'] as $oid) {
            $members = [];
            foreach (array_keys(SnmpQuery::hideMib()->numericIndex()->walk($oid)->throwIfConnectionError()->pluck(Str::after($oid, '::'))) as $index) {
                $parts = explode('.', (string) $index);
                $ifIndex = (int) array_pop($parts);
                $members[$ifIndex] = implode('.', $parts); // VRF name index, ifIndex
            }

            if (! empty($members)) {
                return $this->vrfMemberPorts($members, $vrfs);
            }
        }

        return new Collection;
    }

    /**
     * Set ifVrf of the member ports
     *
     * @param  array<int, int|string>  $members  ifIndex to vrf_oid
     * @param  Collection<int, Vrf>  $vrfs
     * @return Collection<int, Port>
     */
    protected function vrfMemberPorts(array $members, Collection $vrfs): Collection
    {
        $vrfIds = $vrfs->pluck('vrf_id', 'vrf_oid');
        $ports = new Collection;

        foreach ($members as $ifIndex => $vrfOid) {
            $port = PortCache::getByIfIndex($ifIndex, $this->getDeviceId());
            $vrfId = $vrfIds->get((string) $vrfOid);

            if ($port === null || $vrfId === null) {
                Log::debug("Skipping interface $ifIndex of VRF $vrfOid, interface or VRF not found");
                continue;
            }

            $port->ifVrf = $vrfId;
            $ports->push($port);
        }

        return $ports;
    }

    /**
     * @return Collection<int, Vrf>
     */
    private function discoverMplsVpnVrfs(string $rdOid, string $descriptionOid): Collection
    {
        $rds = SnmpQuery::hideMib()->numericIndex()->walk($rdOid)->throwIfConnectionError();
        if (! $rds->isValid()) {
            return new Collection;
        }

        $descriptions = SnmpQuery::hideMib()->numericIndex()->walk($descriptionOid)->pluck(Str::after($descriptionOid, '::'));

        return collect($rds->pluck(Str::after($rdOid, '::')))->map(fn ($rd, $index) => new Vrf([
            'vrf_oid' => (string) $index,
            'vrf_name' => Oid::stringFromOid((string) $index),
            'mplsVpnVrfRouteDistinguisher' => RouteDistinguisher::parse($rd),
            'mplsVpnVrfDescription' => trim((string) ($descriptions[$index] ?? '')),
        ]))->values();
    }
}
