<?php

/**
 * NdpResultsController.php
 *
 * Search page source: IPv6 neighbor discovery tables
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

use App\Models\Ipv6Nd;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\SearchTerm;

/**
 * @extends SearchResultsController<Ipv6Nd>
 */
class NdpResultsController extends SearchResultsController
{
    public static function appliesTo(SearchTerm $term): bool
    {
        return $term->isIpv6() || $term->isMac() || $term->isVendor();
    }

    public static function label(): string
    {
        return __('search.ndp_tables');
    }

    public static function icon(): string
    {
        return 'fa-table';
    }

    public static function description(): string
    {
        return __('search.sources.ndp');
    }

    protected function view(): string
    {
        return 'search.results.neighbors';
    }

    protected function baseQuery(User $user): Builder
    {
        return Ipv6Nd::hasAccess($user)
            ->with(['device', 'port'])
            ->orderBy(DB::raw('INET6_ATON(ipv6_nd.ipv6_address)'))
            ->orderBy('ipv6_nd.device_id');
    }

    protected function search(Builder $query, SearchTerm $term): Builder
    {
        return $query->where(function (Builder $query) use ($term): void {
            if ($term->isIpv6()) {
                $query->where(fn (Builder $q) => $this->whereIp($q, 'ipv6_nd.ipv6_address', $term, $term->ip?->uncompressed() ?? '', DB::raw('INET6_NTOA(INET6_ATON(ipv6_nd.ipv6_address))')));
            }
            if ($term->isMac() || $term->isVendor()) {
                $query->orWhere(fn (Builder $q) => $this->whereMac($q, 'ipv6_nd.mac_address', $term));
            }
        });
    }

    protected function filterDevice(Builder $query, int $deviceId): Builder
    {
        return $query->where('ipv6_nd.device_id', $deviceId);
    }

    protected function viewData(Collection $items, User $user): array
    {
        $macs = $items->pluck('mac_address');

        return [
            'addressColumn' => 'ipv6_address',
            'owners' => $this->portsWithMac($macs, $user),
            'locations' => $this->fdbLocations($macs, $user)->groupBy('mac_address'),
        ];
    }
}
