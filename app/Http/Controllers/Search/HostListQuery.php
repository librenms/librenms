<?php

/**
 * HostListQuery.php
 *
 * Merge the address tables into one row per host, so several sources can be searched and paged together
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
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv4Mac;
use App\Models\Ipv6Address;
use App\Models\Ipv6Nd;
use App\Models\Port;
use App\Models\PortsFdb;
use App\Models\User;
use App\Models\Vlan;
use Illuminate\Contracts\Database\Query\Builder as AnyBuilder;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\IP;
use LibreNMS\Util\IPv6;
use LibreNMS\Util\SearchTerm;

class HostListQuery
{
    public const SOURCES = ['neighbor', 'fdb', 'interface', 'device'];

    /** Ignored everywhere: incomplete ARP and ND entries, and broadcast */
    public const JUNK_MACS = ['', '000000000000', 'ffffffffffff'];

    private const PORT_FILTERS = ['device_id', 'device.groups.id', 'port_id', 'ifAlias', 'ifType'];

    /**
     * @param  list<SearchTerm>  $terms  empty lists every host
     * @param  array<string, array<string, mixed>>  $filters
     */
    public function __construct(private readonly array $terms, private readonly array $filters, private readonly User $user)
    {
    }

    /**
     * One row per IP, or per MAC when no IP is known for it. A search for MACs has one row per MAC instead,
     * a single MAC can have hundreds of addresses.
     *
     * @param  string  $sort  ip, mac, seen or input: the order the terms were given in
     */
    public function build(string $sort = 'ip', string $direction = 'asc'): QueryBuilder
    {
        $direction = $direction == 'desc' ? 'desc' : 'asc';
        // ends in what makes a row unique, pages and export chunks are separate queries that must agree on the order
        $byIp = "MIN(ip) IS NULL, LENGTH(INET6_ATON(MIN(ip))) $direction, INET6_ATON(MIN(ip)) $direction, {$this->groupKey()}";

        $query = $this->rows();

        return match ($sort) {
            'mac' => $query->orderByRaw("MIN(mac) IS NULL, MIN(mac) $direction, $byIp"),
            'seen' => $query->leftJoinSub($this->lastSeen(), 'seen', 'seen.mac_address', '=', 'hosts.mac')
                ->orderByRaw("MAX(seen.seen) IS NULL, MAX(seen.seen) $direction, $byIp"),
            'input' => $this->orderAsGiven($query)->orderByRaw($byIp),
            default => $query->orderByRaw($byIp),
        };
    }

    public function defaultSort(): string
    {
        return count($this->terms) > 1 ? 'input' : 'ip';
    }

    /**
     * What a row of the built query names for HostLookup: its IP, or its MAC when no IP is known or the rows are grouped by MAC
     *
     * @param  array<array-key, mixed>  $row
     */
    public function hostKey(array $row): HostKey
    {
        $mac = is_string($row['mac'] ?? null) ? $row['mac'] : null;
        $ip = is_string($row['ip'] ?? null) ? IP::parse($row['ip'], true) : null;

        return $ip !== null && ($mac === null || ! $this->groupsByMac())
            ? new HostKey(ips: [$ip])
            : new HostKey(macs: $mac !== null ? [$mac] : []);
    }

    public function groupsByMac(): bool
    {
        return $this->terms !== [] && array_all($this->terms, fn (SearchTerm $term): bool => $term->isAddressLike());
    }

    /**
     * How many of the addresses in a pasted list nothing knows about
     *
     * @param  int  $total  how many rows the built query has
     */
    public function notSeenCount(int $total): int
    {
        if ($this->notFoundRows() === []) {
            return 0;
        }

        return $total - $this->rows(withNotFound: false)->getCountForPagination();
    }

    private function rows(bool $withNotFound = true): QueryBuilder
    {
        return DB::query()->fromSub($this->union($withNotFound), 'hosts')
            ->selectRaw('MIN(ip) as ip, MIN(mac) as mac')
            ->groupByRaw($this->groupKey());
    }

    /**
     * What makes a row unique
     */
    private function groupKey(): string
    {
        return $this->groupsByMac() ? 'COALESCE(mac, ip)' : 'COALESCE(ip, mac)';
    }

    private function union(bool $withNotFound = true): QueryBuilder
    {
        $branches = [];
        foreach (self::SOURCES as $source) {
            if ($this->isSourceSelected($source)) {
                array_push($branches, ...$this->branches($source));
            }
        }
        if ($withNotFound) {
            array_push($branches, ...$this->notFoundRows());
        }

        $union = array_shift($branches) ?? DB::query()->selectRaw('NULL as ip, NULL as mac')->whereRaw('1 = 0');
        foreach ($branches as $branch) {
            $union->union($branch);
        }

        return $union;
    }

    /**
     * @return list<QueryBuilder>
     */
    private function branches(string $source): array
    {
        return array_values(array_filter(match ($source) {
            'neighbor' => [
                $this->neighborTable(Ipv4Mac::hasAccess($this->user), 'ipv4_mac', 'ipv4_address', fn (SearchTerm $term) => $term->isIpv4()),
                $this->neighborTable(Ipv6Nd::hasAccess($this->user), 'ipv6_nd', 'ipv6_address', fn (SearchTerm $term) => $term->isIpv6()),
            ],
            'fdb' => [$this->fdb()],
            'interface' => [
                $this->interfaceIps(Ipv4Address::hasAccess($this->user), 'ipv4_addresses', 'ipv4_address', fn (SearchTerm $term) => $term->isIpv4()),
                $this->interfaceIps(Ipv6Address::hasAccess($this->user), 'ipv6_addresses', 'ipv6_address', fn (SearchTerm $term) => $term->isIpv6(), 'ipv6_addresses.ipv6_compressed'),
                $this->interfaceMacs(),
            ],
            'device' => [$this->devices()],
            default => [],
        }));
    }

    /**
     * When each MAC was last seen, ARP entries have no time
     */
    private function lastSeen(): QueryBuilder
    {
        $seen = DB::table('ports_fdb')->select('mac_address', 'updated_at')
            ->unionAll(DB::table('ipv6_nd')->select('mac_address', 'updated_at'));

        return DB::query()->fromSub($seen, 'entries')->selectRaw('mac_address, MAX(updated_at) as seen')->groupBy('mac_address');
    }

    /**
     * Terms that don't name one address go last
     */
    private function orderAsGiven(QueryBuilder $query): QueryBuilder
    {
        $given = [];
        foreach ($this->terms as $term) {
            $given[] = $term->macHex ?? (string) $term->ip?->compressed();
        }
        if ($given === []) {
            return $query;
        }

        $place = fn (string $column): string => "CASE $column " . implode(' ', array_map(fn (int $index): string => 'WHEN ? THEN ' . ($index + 1), array_keys($given))) . ' END';

        return $query->orderByRaw("COALESCE({$place('MIN(ip)')}, {$place('MIN(mac)')}, " . (count($given) + 1) . ')', [...$given, ...$given]);
    }

    /**
     * An ARP or ND table, $isSameFamily says if a term's IP could be in it
     *
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @param  \Closure(SearchTerm): bool  $isSameFamily
     */
    private function neighborTable(Builder $query, string $table, string $ipColumn, \Closure $isSameFamily): ?QueryBuilder
    {
        $terms = $this->termsWhere(fn (SearchTerm $term) => $isSameFamily($term) || $term->isAddressLike());
        if ($this->cannotMatch($terms)) {
            return null;
        }

        $query->whereNotIn("$table.mac_address", self::JUNK_MACS);
        $this->matchAny($query, $terms, function (Builder $q, SearchTerm $term) use ($table, $isSameFamily): void {
            if ($isSameFamily($term)) {
                $this->whereNeighborIp($q, $term);
            }
            $this->orWhereMac($q, "$table.mac_address", $term);
        });

        return $this->scopeToVlan($this->scopeNeighborsToPorts($query, $table), $table)
            ->toBase()->select(DB::raw("INET6_NTOA(INET6_ATON($table.$ipColumn)) as ip"), "$table.mac_address as mac");
    }

    /**
     * With the neighbor tables searched, only the MACs they didn't resolve to an IP: the rest are listed by their IP.
     * On its own this also finds an IP, through the MAC the neighbor tables know for it.
     */
    private function fdb(): ?QueryBuilder
    {
        $withNeighbors = $this->isSourceSelected('neighbor');
        $terms = $this->termsWhere(fn (SearchTerm $term) => $term->isAddressLike() || (! $withNeighbors && $term->isIp()));
        if ($this->cannotMatch($terms)) {
            return null;
        }

        $query = PortsFdb::hasAccess($this->user)->whereNotIn('ports_fdb.mac_address', self::JUNK_MACS);
        $this->matchAny($query, $terms, function (Builder $q, SearchTerm $term): void {
            $this->orWhereMac($q, 'ports_fdb.mac_address', $term);
            if ($term->isIp()) {
                $neighbors = $term->isIpv6() ? Ipv6Nd::hasAccess($this->user)->select('ipv6_nd.mac_address') : Ipv4Mac::hasAccess($this->user)->select('ipv4_mac.mac_address');
                $q->orWhereIn('ports_fdb.mac_address', $this->whereNeighborIp($neighbors, $term));
            }
        });
        if ($withNeighbors) {
            $this->exceptMacsWithIp($query, 'ports_fdb.mac_address');
        }

        return $this->scopeToVlan($this->scopeToPorts($query, 'ports_fdb'), 'ports_fdb')
            ->toBase()->select(DB::raw('NULL as ip'), 'ports_fdb.mac_address as mac');
    }

    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @param  \Closure(SearchTerm): bool  $applies
     */
    private function interfaceIps(Builder $query, string $table, string $column, \Closure $applies, ?string $alternateColumn = null): ?QueryBuilder
    {
        $terms = $this->termsWhere($applies);
        if ($this->cannotMatch($terms) || $this->hasFilter('vlan')) {
            return null;
        }

        $this->matchAny($query, $terms, fn (Builder $q, SearchTerm $term) => $this->whereIp($q, "$table.$column", $term, $alternateColumn));

        $query->join('ports', 'ports.port_id', '=', "$table.port_id");

        return $this->scopeToPorts($query, $table)
            ->toBase()->select(DB::raw("INET6_NTOA(INET6_ATON($table.$column)) as ip"), 'ports.ifPhysAddress as mac');
    }

    /**
     * MACs of interfaces without an address, ports sharing a MAC collapse into one row
     */
    private function interfaceMacs(): ?QueryBuilder
    {
        $terms = $this->termsWhere(fn (SearchTerm $term) => $term->isAddressLike());
        if ($this->cannotMatch($terms) || $this->hasFilter('vlan')) {
            return null;
        }

        $query = Port::hasAccess($this->user)->isNotDeleted()->whereNotNull('ports.ifPhysAddress')->where('ports.ifPhysAddress', '!=', '');
        $this->matchAny($query, $terms, fn (Builder $q, SearchTerm $term) => $this->whereMac($q, 'ports.ifPhysAddress', $term));
        if ($this->isSourceSelected('neighbor')) {
            $this->exceptMacsWithIp($query, 'ports.ifPhysAddress');
        }
        $query->whereNotIn('ports.ifPhysAddress', self::JUNK_MACS);
        $query->whereNotIn('ports.ifPhysAddress', fn ($sub) => $sub->from('ports as with_ip')->select('with_ip.ifPhysAddress')
            ->whereNotNull('with_ip.ifPhysAddress')
            ->where(fn ($has) => $has->whereIn('with_ip.port_id', fn ($v4) => $v4->from('ipv4_addresses')->select('port_id'))
                ->orWhereIn('with_ip.port_id', fn ($v6) => $v6->from('ipv6_addresses')->select('port_id'))));

        return $this->scopeToPorts($query, 'ports')->toBase()->select(DB::raw('NULL as ip'), 'ports.ifPhysAddress as mac');
    }

    private function devices(): ?QueryBuilder
    {
        if ($this->filteredPorts() !== null || $this->hasFilter('vlan')) {
            return null;
        }

        $query = Device::hasAccess($this->user)->whereNotNull('devices.ip')
            ->leftJoin('locations', 'devices.location_id', '=', 'locations.id');

        $this->matchAny($query, $this->terms, function (Builder $q, SearchTerm $term): void {
            $like = $term->like();
            $q->whereLike('devices.hostname', $like)
                ->orWhereLike('devices.sysName', $like)
                ->orWhereLike('devices.display', $like);

            if ($term->mayBeText()) {
                $q->orWhereLike('devices.hardware', $like)
                    ->orWhereLike('devices.serial', $like)
                    ->orWhereLike('devices.purpose', $like)
                    ->orWhereLike('devices.notes', $like)
                    ->orWhereLike('locations.location', $like);
            }

            if ($range = $term->networkRange()) {
                // the column holds both families, and hex strings of different lengths can sort into each other's range
                $q->orWhereRaw('LENGTH(devices.ip) = ? AND HEX(devices.ip) BETWEEN ? AND ?', [strlen($range[0]) / 2, ...$range]);
            } elseif ($term->isExactIp()) {
                $q->orWhere('devices.ip', $term->ip?->packed());
            }
        });

        return $query->toBase()->selectRaw('INET6_NTOA(devices.ip) as ip, NULL as mac');
    }

    /**
     * Pasted addresses that nothing matched still get a row, so the list shows they were not seen
     *
     * @return list<QueryBuilder>
     */
    private function notFoundRows(): array
    {
        if (count($this->terms) < 2 || $this->filters !== []) {
            return [];
        }

        // a MAC the neighbor tables know is already listed by its IP
        $macs = array_values(array_filter(array_map(fn (SearchTerm $term) => $term->isFullMac() ? $term->macHex : null, $this->terms)));
        $withIp = Ipv4Mac::hasAccess($this->user)->whereIn('ipv4_mac.mac_address', $macs)->pluck('mac_address')
            ->merge(Ipv6Nd::hasAccess($this->user)->whereIn('ipv6_nd.mac_address', $macs)->pluck('mac_address'));

        $rows = [];
        foreach ($this->terms as $term) {
            if ($term->isExactIp()) {
                $rows[] = DB::query()->selectRaw('? as ip, NULL as mac', [$term->ip?->compressed()]);
            } elseif ($term->isFullMac() && ! $withIp->contains($term->macHex)) {
                $rows[] = DB::query()->selectRaw('NULL as ip, ? as mac', [$term->macHex]);
            }
        }

        return $rows;
    }

    /**
     * @param  \Closure(SearchTerm): bool  $applies
     * @return list<SearchTerm>
     */
    private function termsWhere(\Closure $applies): array
    {
        return array_values(array_filter($this->terms, $applies));
    }

    /**
     * This search has terms, but none of them could be found in a source
     *
     * @param  list<SearchTerm>  $applicableTerms
     */
    private function cannotMatch(array $applicableTerms): bool
    {
        return $this->terms !== [] && $applicableTerms === [];
    }

    /**
     * Without terms this matches everything
     *
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @param  list<SearchTerm>  $terms
     * @param  \Closure(Builder<covariant \Illuminate\Database\Eloquent\Model>, SearchTerm): mixed  $match
     */
    private function matchAny(Builder $query, array $terms, \Closure $match): void
    {
        if ($terms === []) {
            return;
        }

        $query->where(function (Builder $any) use ($terms, $match): void {
            foreach ($terms as $term) {
                $any->orWhere(fn (Builder $q) => $match($q, $term));
            }
        });
    }

    /**
     * Match the IP column of the neighbor table for this term's address family
     *
     * @template TQuery of AnyBuilder
     *
     * @param  TQuery  $query
     * @return TQuery
     */
    private function whereNeighborIp(AnyBuilder $query, SearchTerm $term): AnyBuilder
    {
        return $term->isIpv6()
            ? $this->whereIp($query, 'ipv6_nd.ipv6_address', $term, DB::raw('INET6_NTOA(INET6_ATON(ipv6_nd.ipv6_address))'))
            : $this->whereIp($query, 'ipv4_mac.ipv4_address', $term);
    }

    /**
     * Match an IP address column: exact address, network or partial address.
     * Address columns store IPv4 compressed and IPv6 uncompressed.
     *
     * @template TQuery of AnyBuilder
     *
     * @param  TQuery  $query
     * @param  string|Expression|null  $alternateColumn  the same address compressed, to find partial IPv6 addresses
     * @return TQuery
     */
    private function whereIp(AnyBuilder $query, string $column, SearchTerm $term, string|Expression|null $alternateColumn = null): AnyBuilder
    {
        if ($range = $term->networkRange()) {
            return $query->whereRaw("HEX(INET6_ATON($column)) BETWEEN ? AND ?", $range);
        }

        if ($term->isExactIp()) {
            $exact = $term->ip instanceof IPv6 ? $term->ip->uncompressed() : $term->ip?->compressed();
            if ($alternateColumn && $term->mayBePartialIpv6()) {
                return $query->where(fn (AnyBuilder $q) => $q->where($column, $exact)->orWhereLike($alternateColumn, $term->ip?->compressed() . '%'));
            }

            return $query->where($column, $exact);
        }

        return $query->where(fn (AnyBuilder $q) => $q->whereLike($column, $term->like())
            ->when($alternateColumn, fn (AnyBuilder $q) => $q->orWhereLike($alternateColumn, $term->like())));
    }

    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     */
    private function orWhereMac(Builder $query, string $column, SearchTerm $term): void
    {
        if ($term->isAddressLike()) {
            $query->orWhere(fn (Builder $mac) => $this->whereMac($mac, $column, $term));
        }
    }

    /**
     * Match a MAC address column (12 lowercase hex digits): partial MAC or vendor
     *
     * @template TQuery of AnyBuilder
     *
     * @param  TQuery  $query
     * @return TQuery
     */
    private function whereMac(AnyBuilder $query, string $column, SearchTerm $term): AnyBuilder
    {
        if ($term->isMac()) {
            return $term->isFullMac() ? $query->where($column, $term->macHex) : $query->whereLike($column, "%$term->macHex%");
        }

        $ouis = $term->vendorOuis();

        return $query->where(function (AnyBuilder $q) use ($column, $ouis): void {
            $q->whereIn(DB::raw('SUBSTR(' . DB::getQueryGrammar()->wrap($column) . ', 1, 6)'), $ouis['short']);
            foreach ($ouis['long'] as $oui) {
                $q->orWhereLike($column, "$oui%");
            }
        });
    }

    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     */
    private function exceptMacsWithIp(Builder $query, string $column): void
    {
        $query->whereNotIn($column, Ipv4Mac::hasAccess($this->user)->select('ipv4_mac.mac_address'))
            ->whereNotIn($column, Ipv6Nd::hasAccess($this->user)->select('ipv6_nd.mac_address'));
    }

    /**
     * The ports the filters ask for, null when they don't narrow the ports
     *
     * @return Builder<Port>|null
     */
    private function filteredPorts(): ?Builder
    {
        $portFilters = array_intersect_key($this->filters, array_flip(self::PORT_FILTERS));

        return $portFilters ? Port::query()->applyFilters($portFilters)->select('ports.port_id') : null;
    }

    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<covariant \Illuminate\Database\Eloquent\Model>
     */
    private function scopeToPorts(Builder $query, string $table): Builder
    {
        $ports = $this->filteredPorts();

        return $ports ? $query->whereIn("$table.port_id", $ports) : $query;
    }

    /**
     * A router learns the IP but the switch port is in the forwarding table, either one can be what is asked for
     *
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<covariant \Illuminate\Database\Eloquent\Model>
     */
    private function scopeNeighborsToPorts(Builder $query, string $table): Builder
    {
        $ports = $this->filteredPorts();

        return $ports ? $query->where(fn (Builder $q) => $q->whereIn("$table.port_id", $ports)
            ->orWhereIn("$table.mac_address", PortsFdb::hasAccess($this->user)->whereIn('ports_fdb.port_id', $ports)->select('ports_fdb.mac_address'))) : $query;
    }

    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<covariant \Illuminate\Database\Eloquent\Model>
     */
    private function scopeToVlan(Builder $query, string $table): Builder
    {
        return $this->hasFilter('vlan') ? $query->whereIn("$table.mac_address", $this->macsInVlan()) : $query;
    }

    private function hasFilter(string $key): bool
    {
        return isset($this->filters[$key]);
    }

    private function macsInVlan(): QueryBuilder
    {
        return DB::table('ports_fdb')->select('mac_address')
            ->whereIn('vlan_id', Vlan::query()->applyFilters(['vlan_vlan' => $this->filters['vlan']])->select('vlan_id'));
    }

    private function isSourceSelected(string $source): bool
    {
        $filter = $this->filters['source'] ?? [];
        foreach ($filter as $op => $value) {
            $values = is_array($value) ? $value : explode(',', (string) $value);
            $listed = in_array($source, $values);
            if ((in_array($op, ['eq', 'in']) && ! $listed) || (in_array($op, ['neq', 'not_in']) && $listed)) {
                return false;
            }
        }

        return true;
    }
}
