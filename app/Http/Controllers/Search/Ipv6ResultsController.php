<?php

/**
 * Ipv6ResultsController.php
 *
 * Search page source: IPv6 addresses configured on device interfaces
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

use App\Models\Ipv6Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\SearchTerm;

class Ipv6ResultsController extends SearchResultsController
{
    public static function appliesTo(SearchTerm $term): bool
    {
        return $term->isIpv6();
    }

    public static function label(): string
    {
        return __('search.ipv6_addresses');
    }

    public static function icon(): string
    {
        return 'fa-sitemap';
    }

    public static function description(): string
    {
        return __('search.sources.ipv6');
    }

    protected function view(): string
    {
        return 'search.results.addresses';
    }

    protected function baseQuery(User $user): Builder
    {
        return Ipv6Address::hasAccess($user)
            ->with(['port.device', 'network'])
            ->orderBy(DB::raw('INET6_ATON(ipv6_address)'))
            ->orderBy('port_id');
    }

    protected function search(Builder $query, SearchTerm $term): Builder
    {
        return $this->whereIp($query, 'ipv6_address', $term, $term->ip?->uncompressed() ?? '', 'ipv6_compressed');
    }

    protected function filterDevice(Builder $query, int $deviceId): Builder
    {
        return $query->whereHas('port', fn (Builder $q) => $q->where('device_id', $deviceId));
    }
}
