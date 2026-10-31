<?php

/**
 * ArpResultsController.php
 *
 * Search page source: IPv4 ARP tables
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
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\SearchTerm;

class ArpResultsController extends SearchResultsController
{
    public static function appliesTo(SearchTerm $term): bool
    {
        return $term->isIpv4() || $term->isMac() || $term->isVendor();
    }

    public static function label(): string
    {
        return __('search.arp_tables');
    }

    public static function icon(): string
    {
        return 'fa-table';
    }

    public static function description(): string
    {
        return __('search.sources.arp');
    }

    protected function view(): string
    {
        return 'search.results.neighbors';
    }

    protected function baseQuery(User $user): Builder
    {
        return Ipv4Mac::hasAccess($user)
            ->with(['device', 'port'])
            ->orderBy(DB::raw('INET_ATON(ipv4_mac.ipv4_address)'))
            ->orderBy('ipv4_mac.device_id');
    }

    protected function search(Builder $query, SearchTerm $term): Builder
    {
        return $query->where(function (Builder $query) use ($term): void {
            if ($term->isIpv4()) {
                $query->where(fn (Builder $q) => $this->whereIp($q, 'ipv4_mac.ipv4_address', $term, $term->ip?->compressed() ?? ''));
            }
            if ($term->isMac() || $term->isVendor()) {
                $query->orWhere(fn (Builder $q) => $this->whereMac($q, 'ipv4_mac.mac_address', $term));
            }
        });
    }

    protected function filterDevice(Builder $query, int $deviceId): Builder
    {
        return $query->where('ipv4_mac.device_id', $deviceId);
    }

    protected function viewData(Collection $items, User $user): array
    {
        $macs = $items->pluck('mac_address');

        return [
            'addressColumn' => 'ipv4_address',
            'owners' => $this->portsWithMac($macs, $user),
            'locations' => $this->fdbLocations($macs, $user)->groupBy('mac_address'),
        ];
    }
}
