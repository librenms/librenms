<?php

/**
 * MacResultsController.php
 *
 * Search page source: MAC addresses of device interfaces
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

use App\Models\Port;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Util\SearchTerm;

class MacResultsController extends SearchResultsController
{
    public static function appliesTo(SearchTerm $term): bool
    {
        return $term->isMac() || $term->isVendor();
    }

    public static function label(): string
    {
        return __('search.interface_macs');
    }

    public static function icon(): string
    {
        return 'fa-ethernet';
    }

    public static function description(): string
    {
        return __('search.sources.mac');
    }

    protected function view(): string
    {
        return 'search.results.mac';
    }

    protected function baseQuery(User $user): Builder
    {
        return Port::hasAccess($user)->isNotDeleted()
            ->whereNotNull('ports.ifPhysAddress')
            ->where('ports.ifPhysAddress', '!=', '')
            ->with('device')
            ->orderBy('ports.ifPhysAddress')
            ->orderBy('ports.device_id')
            ->orderBy('ports.ifIndex');
    }

    protected function search(Builder $query, SearchTerm $term): Builder
    {
        return $this->whereMac($query, 'ports.ifPhysAddress', $term);
    }

    protected function filterDevice(Builder $query, int $deviceId): Builder
    {
        return $query->where('ports.device_id', $deviceId);
    }
}
