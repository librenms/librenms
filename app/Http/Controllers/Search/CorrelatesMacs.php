<?php

/**
 * CorrelatesMacs.php
 *
 * Find which monitored ports own, learned or resolved a set of MAC addresses
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

namespace App\Http\Controllers\Search;

use App\Models\Ipv4Mac;
use App\Models\Ipv6Nd;
use App\Models\Port;
use App\Models\PortsFdb;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\IP;

trait CorrelatesMacs
{
    /**
     * Monitored ports that own one of these MAC addresses, keyed by MAC
     *
     * @param  Collection<int, string>  $macs
     * @return Collection<array-key, \Illuminate\Database\Eloquent\Collection<int, Port>>
     */
    protected function portsWithMac(Collection $macs, User $user): Collection
    {
        $macs = $this->usableMacs($macs);
        if ($macs->isEmpty()) {
            return new Collection;
        }

        return Port::hasAccess($user)->isNotDeleted()
            ->with('device')
            ->whereIn('ifPhysAddress', $macs)
            ->get()
            ->groupBy(fn (Port $port): string => (string) $port->getRawOriginal('ifPhysAddress'));
    }

    /**
     * Forwarding table entries for these MAC addresses, the most likely access port first
     *
     * @param  Collection<int, string>  $macs
     * @return Collection<int, PortsFdb>
     */
    protected function fdbLocations(Collection $macs, User $user): Collection
    {
        $macs = $this->usableMacs($macs);
        if ($macs->isEmpty()) {
            return new Collection;
        }

        $macCounts = DB::table('ports_fdb')
            ->select('port_id', DB::raw('COUNT(DISTINCT mac_address) as mac_count'))
            ->whereIn('port_id', DB::table('ports_fdb')->select('port_id')->whereIn('mac_address', $macs))
            ->groupBy('port_id');

        return PortsFdb::hasAccess($user)
            ->select(['ports_fdb.*', 'mac_counts.mac_count'])
            ->joinSub($macCounts, 'mac_counts', 'ports_fdb.port_id', '=', 'mac_counts.port_id')
            ->with(['device', 'vlan', 'port' => fn ($q) => $q->withCount('links')])
            ->whereIn('ports_fdb.mac_address', $macs)
            ->get()
            ->sort($this->closestPortFirst(...))
            ->values();
    }

    /**
     * Ports with a discovered neighbor are links to other network devices,
     * after that the port with the fewest learned MACs is closest to the host
     */
    protected function closestPortFirst(PortsFdb $a, PortsFdb $b): int
    {
        return [$a->port?->links_count > 0, (int) $a->mac_count, $b->updated_at]
            <=> [$b->port?->links_count > 0, (int) $b->mac_count, $a->updated_at];
    }

    /**
     * IPv4 and IPv6 addresses resolved to these MAC addresses by ARP or neighbor discovery, keyed by MAC
     *
     * @param  Collection<int, string>  $macs
     * @return Collection<array-key, Collection<int, string>>
     */
    protected function ipsForMacs(Collection $macs, User $user): Collection
    {
        $macs = $this->usableMacs($macs);
        if ($macs->isEmpty()) {
            return new Collection;
        }

        $ipv4 = Ipv4Mac::hasAccess($user)->whereIn('mac_address', $macs)->get(['mac_address', 'ipv4_address'])
            ->map(fn (Ipv4Mac $entry): array => [$entry->mac_address, $entry->ipv4_address]);
        $ipv6 = Ipv6Nd::hasAccess($user)->whereIn('mac_address', $macs)->get(['mac_address', 'ipv6_address'])
            ->map(fn (Ipv6Nd $entry): array => [$entry->mac_address, IP::parse($entry->ipv6_address, true)?->compressed() ?? $entry->ipv6_address]);

        return $ipv4->toBase()->concat($ipv6)
            ->groupBy(fn (array $entry): string => $entry[0])
            ->map(fn (Collection $entries): Collection => $entries->map(fn (array $entry): string => $entry[1])->unique()->values());
    }

    /**
     * Unique MACs, without the broadcast and empty addresses that show up everywhere
     *
     * @param  Collection<int, string>  $macs
     * @return Collection<int, string>
     */
    private function usableMacs(Collection $macs): Collection
    {
        return collect(array_values(array_diff(array_unique($macs->all()), ['', '000000000000', 'ffffffffffff'])));
    }
}
