<?php

/**
 * Bgp4V2Mib.php
 *
 * BGP sessions from vendor copies of the BGP4-V2-MIB draft, which only differ by MIB and table name.
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

use App\Models\BgpPeer;
use App\Models\BgpPeerCbgp;
use Illuminate\Support\Collection;
use LibreNMS\Util\Bgp;

trait Bgp4V2Mib
{
    /**
     * Sessions are indexed by instance and remote address. Some vendors use the VRF as instance,
     * otherwise the table is read in each VRF context. BGP4-MIB is read when the table has no sessions.
     *
     * @param  string  $prefix  table name prefix, for example aristaBgp4V2
     * @param  bool  $instanceIsVrf  the instance is the vrf_oid of the VRF
     * @param  bool  $counters  the MIB has the message counters
     * @return Collection<int, BgpPeer>
     */
    protected function discoverBgp4V2Peers(string $mib, string $prefix, bool $instanceIsVrf = false, bool $counters = true): Collection
    {
        $columns = [
            'bgpPeerRemoteAs' => "{$prefix}PeerRemoteAs",
            'bgpPeerState' => "{$prefix}PeerState",
            'bgpPeerAdminStatus' => "{$prefix}PeerAdminStatus",
            'bgpLocalAddr' => "{$prefix}PeerLocalAddr",
            'bgpPeerDescr' => "{$prefix}PeerDescription",
            'bgpPeerLastErrorCode' => "{$prefix}PeerLastErrorCodeReceived",
            'bgpPeerLastErrorSubCode' => "{$prefix}PeerLastErrorSubCodeReceived",
            'bgpPeerLastErrorText' => "{$prefix}PeerLastErrorReceivedText",
            'bgpPeerFsmEstablishedTime' => "{$prefix}PeerFsmEstablishedTime",
            'bgpPeerInUpdateElapsedTime' => "{$prefix}PeerInUpdatesElapsedTime",
        ];

        if ($counters) {
            $columns += [
                'bgpPeerInUpdates' => "{$prefix}PeerInUpdates",
                'bgpPeerOutUpdates' => "{$prefix}PeerOutUpdates",
                'bgpPeerInTotalMessages' => "{$prefix}PeerInTotalMessages",
                'bgpPeerOutTotalMessages' => "{$prefix}PeerOutTotalMessages",
            ];
        }

        $peers = new Collection;
        foreach ($instanceIsVrf ? [''] : $this->getDevice()->getVrfContexts() as $context) {
            $contextVrfId = $this->getDevice()->vrfIdForContext($context);

            $found = collect($this->walkBgpTable($context, $mib, $columns))->map(function (array $row, int|string $index) use ($context, $contextVrfId, $instanceIsVrf): ?BgpPeer {
                $parts = explode('.', (string) $index);
                $instance = array_shift($parts);
                $address = Bgp::shiftInetAddress($parts, 0, $zone);

                return $this->makeBgpPeer($address, $zone, $context, $instanceIsVrf ? $this->bgpVrfId($instance) : $contextVrfId, $row);
            })->filter();

            $peers = $peers->merge($found->isNotEmpty() ? $found : $this->discoverBgp4MibPeers($context));
        }

        return $peers->values();
    }

    /**
     * Prefix counts are indexed by instance, remote address, afi and safi
     *
     * @param  Collection<int, BgpPeer>  $peers
     * @param  string  $prefix  table name prefix, for example aristaBgp4V2
     * @return Collection<int, BgpPeerCbgp>
     */
    protected function discoverBgp4V2AddressFamilies(Collection $peers, string $mib, string $prefix): Collection
    {
        $families = [];

        foreach ($peers->pluck('context_name')->unique() as $context) {
            $rows = $this->walkBgpTable($context, $mib, [
                'AcceptedPrefixes' => "{$prefix}PrefixInPrefixesAccepted",
                'AdvertisedPrefixes' => "{$prefix}PrefixOutPrefixes",
            ]);

            foreach ($rows as $index => $counters) {
                $parts = explode('.', (string) $index);
                array_shift($parts); // instance
                $address = Bgp::shiftInetAddress($parts, 2, $zone);
                if (count($parts) == 2) {
                    $families[] = $this->makeBgpAddressFamily($address, $zone, $context, $parts[0], $parts[1], $counters);
                }
            }
        }

        return (new Collection($families))->filter()->values();
    }
}
