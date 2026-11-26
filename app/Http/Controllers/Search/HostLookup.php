<?php

/**
 * HostLookup.php
 *
 * Gather what the address tables know about hosts: bindings, ports and owners
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

use App\Http\Controllers\Search\Data\HostKey;
use App\Http\Controllers\Search\Data\HostLocation;
use App\Http\Controllers\Search\Data\HostMac;
use App\Http\Controllers\Search\Data\HostRecord;
use App\Http\Controllers\Search\Data\NeighborEntry;
use App\Models\Device;
use App\Models\Ipv4Mac;
use App\Models\Ipv6Nd;
use App\Models\Port;
use App\Models\PortsFdb;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\IP;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\IPv6;

class HostLookup
{
    /** Entries further apart than this are old data */
    private const int STALE_AFTER_SECONDS = 86400;

    /** Keeps a MAC with a huge number of addresses in check, scaled so hosts in a batch don't take each other's share */
    private const int MAX_ENTRIES_PER_HOST = 2000;

    public function __construct(private readonly User $user)
    {
    }

    /**
     * @param  list<HostKey>  $hosts
     * @param  bool  $follow  also find the other addresses of the MACs found, a slower lookup for a single host
     * @return list<HostRecord>
     */
    public function find(array $hosts, bool $follow = false): array
    {
        $allIps = collect($hosts)->flatMap(fn (HostKey $host): array => $host->ips)->values();
        $allMacs = collect($hosts)->flatMap(fn (HostKey $host): array => $host->macs)->values();

        $limit = self::MAX_ENTRIES_PER_HOST * count($hosts);
        $entries = $this->entries($allIps, $allMacs, $limit);
        if ($follow) {
            // added to what was found, a MAC with more addresses than the limit can't push out the IP asked for
            $entries = $entries->concat($this->entries(collect(), $allMacs->merge($entries->pluck('mac'))->unique()->values(), $limit))
                ->unique(fn (NeighborEntry $entry): string => $entry->key())
                ->values();
        }

        $interfaces = new SubnetLookup($this->user)->portsWithIps($allIps);
        $devices = Device::hasAccess($this->user)->whereIn('ip', $allIps->map(fn (IP $ip) => $ip->packed()))->get()
            ->keyBy(fn (Device $device) => (string) IP::parse((string) $device->ip, true)?->compressed());

        $macs = $allMacs->merge($entries->pluck('mac'))
            ->merge($interfaces->map(fn (Port $port) => (string) $port->getRawOriginal('ifPhysAddress'))->values())
            ->unique()->diff(HostListQuery::JUNK_MACS)->values();
        $locations = $this->fdbLocations($macs)->groupBy('mac');
        $owners = Port::hasAccess($this->user)->isNotDeleted()->with('device')->whereIn('ifPhysAddress', $macs)->get()
            ->groupBy(fn (Port $port): string => (string) $port->getRawOriginal('ifPhysAddress'));
        $neighborSeen = Ipv6Nd::hasAccess($this->user)->whereIn('mac_address', $macs)->selectRaw('mac_address, MAX(updated_at) as seen')->groupBy('mac_address')->toBase()->pluck('seen', 'mac_address');

        return array_map(function (HostKey $host) use ($entries, $interfaces, $devices, $locations, $owners, $neighborSeen, $follow): HostRecord {
            $wanted = $host->compressedIps();
            $isWanted = fn (NeighborEntry $entry): bool => in_array($entry->ip->compressed(), $wanted);
            $matched = $entries->filter(fn (NeighborEntry $entry): bool => $isWanted($entry) || in_array($entry->mac, $host->macs));

            // the other addresses of a MAC tell us about this host, unless it's unclear which MAC is the host
            $matchedMacs = $matched->pluck('mac')->unique();
            if ($follow && $matchedMacs->count() == 1) {
                $matched = $entries->filter(fn (NeighborEntry $entry): bool => $isWanted($entry) || $matchedMacs->contains($entry->mac));
            }

            $interface = collect($wanted)->map(fn (string $ip) => $interfaces->get($ip))->filter()->first();
            $device = collect($wanted)->map(fn (string $ip) => $devices->get($ip))->filter()->first();

            $hexes = collect($host->macs)->merge($matched->pluck('mac'))
                ->when($interface, fn (Collection $list) => $list->push((string) $interface->getRawOriginal('ifPhysAddress')))
                ->filter()->unique()->values();

            if (! $interface && $hexes->count() == 1) {
                $interface = $owners->get($hexes->first())?->first();
            }

            return new HostRecord(
                ips: collect($host->ips)->merge($matched->pluck('ip'))->unique(fn (IP $ip) => $ip->compressed())->values()->all(),
                macs: $this->describeMacs($hexes, $locations, $neighborSeen),
                locations: $hexes->flatMap(fn (string $mac) => $locations->get($mac, []))->sort($this->closestPortFirst(...))->values()->all(),
                entries: $matched->values()->all(),
                interface: $interface,
                device: $device ?? $interface?->device,
                severalMacs: $this->hasSeveralMacs($matched),
            );
        }, $hosts);
    }

    /**
     * @param  Collection<int, IP>  $ips
     * @param  Collection<int, string>  $macs
     * @return Collection<int, NeighborEntry>
     */
    private function entries(Collection $ips, Collection $macs, int $limit): Collection
    {
        $ipv4 = $ips->whereInstanceOf(IPv4::class)->map(fn (IP $ip) => $ip->compressed())->values();
        $ipv6 = $ips->whereInstanceOf(IPv6::class)->map(fn (IP $ip) => $ip->uncompressed())->values();

        $arp = Ipv4Mac::hasAccess($this->user)->with('port.device')
            ->whereNotIn('mac_address', HostListQuery::JUNK_MACS)
            ->where(fn (Builder $q) => $q->whereIn('ipv4_address', $ipv4)->orWhereIn('mac_address', $macs))
            ->limit($limit)->get()
            ->map(fn (Ipv4Mac $entry): ?NeighborEntry => $this->neighborEntry('ARP', $entry->ipv4_address, $entry));

        $nd = Ipv6Nd::hasAccess($this->user)->with('port.device')
            ->whereNotIn('mac_address', HostListQuery::JUNK_MACS)
            ->where(fn (Builder $q) => $q->whereIn('ipv6_address', $ipv6)->orWhereIn('mac_address', $macs))
            ->limit($limit)->get()
            ->map(fn (Ipv6Nd $entry): ?NeighborEntry => $this->neighborEntry('ND', $entry->ipv6_address, $entry));

        return $arp->toBase()->concat($nd)->filter()->values();
    }

    private function neighborEntry(string $source, string $address, Ipv4Mac|Ipv6Nd $entry): ?NeighborEntry
    {
        $ip = IP::parse($address, true);

        return $ip === null ? null : new NeighborEntry($source, $ip, $entry->mac_address, $entry->port, (string) $entry->context_name);
    }

    /**
     * Forwarding table entries for these MAC addresses
     *
     * @param  Collection<int, string>  $macs
     * @return Collection<int, HostLocation>
     */
    private function fdbLocations(Collection $macs): Collection
    {
        $macCounts = DB::table('ports_fdb')
            ->select('port_id', DB::raw('COUNT(DISTINCT mac_address) as mac_count'))
            ->whereIn('port_id', DB::table('ports_fdb')->select('port_id')->whereIn('mac_address', $macs))
            ->groupBy('port_id');

        return PortsFdb::hasAccess($this->user)
            ->select(['ports_fdb.*', 'mac_counts.mac_count'])
            ->joinSub($macCounts, 'mac_counts', 'ports_fdb.port_id', '=', 'mac_counts.port_id')
            ->with(['port' => fn ($q) => $q->with('device')->withCount('links')])
            ->whereIn('ports_fdb.mac_address', $macs)
            ->get()
            ->toBase()
            ->map(HostLocation::fromFdb(...));
    }

    /**
     * Links to other network devices and aggregates go last,
     * after that the port with the fewest learned MACs is closest to the host
     */
    private function closestPortFirst(HostLocation $a, HostLocation $b): int
    {
        return [$a->isTrunk(), $a->macCount, $b->updatedAt]
            <=> [$b->isTrunk(), $b->macCount, $a->updatedAt];
    }

    /**
     * @param  Collection<int, string>  $hexes
     * @param  Collection<array-key, Collection<int, HostLocation>>  $locations
     * @param  Collection<array-key, mixed>  $neighborSeen
     * @return list<HostMac>
     */
    private function describeMacs(Collection $hexes, Collection $locations, Collection $neighborSeen): array
    {
        $seen = $hexes->mapWithKeys(fn (string $mac): array => [$mac => collect($locations->get($mac, []))->pluck('updatedAt')
            ->push($neighborSeen->get($mac) ? Carbon::parse($neighborSeen->get($mac)) : null)
            ->filter()->max()]);

        // only compare what has a timestamp, ARP entries don't have one
        $newest = $seen->filter()->max();

        return $hexes->map(fn (string $mac): HostMac => new HostMac(
            $mac,
            $seen->get($mac),
            $seen->get($mac) !== null && $seen->get($mac)->diffInSeconds($newest, true) > self::STALE_AFTER_SECONDS,
        ))->values()->all();
    }

    /**
     * @param  Collection<int, NeighborEntry>  $entries
     */
    private function hasSeveralMacs(Collection $entries): bool
    {
        return $entries->reject(fn (NeighborEntry $entry): bool => $entry->ip->isLinkLocal())
            ->groupBy(fn (NeighborEntry $entry): string => $entry->ip->compressed() . '|' . $entry->context)
            ->contains(fn (Collection $same): bool => $same->pluck('mac')->unique()->count() > 1);
    }
}
