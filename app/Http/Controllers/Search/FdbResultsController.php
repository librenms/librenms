<?php

/**
 * FdbResultsController.php
 *
 * Search page source: switch forwarding (FDB) tables
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

use App\Models\PortsFdb;
use App\Models\User;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\SearchTerm;

class FdbResultsController extends SearchResultsController
{
    public static function appliesTo(SearchTerm $term): bool
    {
        return $term->isIp() || $term->isMac() || $term->isVendor();
    }

    public static function label(): string
    {
        return __('search.fdb_tables');
    }

    public static function icon(): string
    {
        return 'fa-plug';
    }

    public static function description(): string
    {
        return __('search.sources.fdb');
    }

    protected function view(): string
    {
        return 'search.results.fdb';
    }

    protected function baseQuery(User $user): Builder
    {
        $macCounts = DB::table('ports_fdb')
            ->select('port_id', DB::raw('COUNT(DISTINCT mac_address) as mac_count'))
            ->groupBy('port_id');

        return PortsFdb::hasAccess($user)
            ->select(['ports_fdb.*', 'mac_counts.mac_count'])
            ->joinSub($macCounts, 'mac_counts', 'ports_fdb.port_id', '=', 'mac_counts.port_id')
            ->with(['device', 'vlan', 'port' => fn ($q) => $q->withCount('links')])
            ->orderBy('mac_counts.mac_count') // ports with a single MAC are most likely the access port
            ->orderBy('ports_fdb.mac_address')
            ->orderBy('ports_fdb.device_id');
    }

    protected function search(Builder $query, SearchTerm $term): Builder
    {
        return $query->where(function (Builder $query) use ($term): void {
            if ($term->isMac() || $term->isVendor()) {
                $query->where(fn (Builder $q) => $this->whereMac($q, 'ports_fdb.mac_address', $term));
            }

            // find the MACs that resolve to the IP
            if ($term->isIpv4()) {
                $query->orWhereExists(fn (QueryBuilder $q) => $this->whereIp(
                    $q->from('ipv4_mac')->whereColumn('ipv4_mac.mac_address', 'ports_fdb.mac_address'),
                    'ipv4_mac.ipv4_address', $term, $term->ip?->compressed() ?? ''
                ));
            } elseif ($term->isIpv6()) {
                $query->orWhereExists(fn (QueryBuilder $q) => $this->whereIp(
                    $q->from('ipv6_nd')->whereColumn('ipv6_nd.mac_address', 'ports_fdb.mac_address'),
                    'ipv6_nd.ipv6_address', $term, $term->ip?->uncompressed() ?? '', DB::raw('INET6_NTOA(INET6_ATON(ipv6_nd.ipv6_address))')
                ));
            }
        });
    }

    protected function filterDevice(Builder $query, int $deviceId): Builder
    {
        return $query->where('ports_fdb.device_id', $deviceId);
    }

    protected function viewData(Collection $items, User $user): array
    {
        $macs = $items->pluck('mac_address');

        return [
            'owners' => $this->portsWithMac($macs, $user),
            'addresses' => $this->ipsForMacs($macs, $user),
        ];
    }
}
