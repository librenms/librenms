<?php

/**
 * DevicesResultsController.php
 *
 * Search page source: monitored devices
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

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Util\SearchTerm;

class DevicesResultsController extends SearchResultsController
{
    public static function appliesTo(SearchTerm $term): bool
    {
        return true;
    }

    public static function label(): string
    {
        return __('search.devices');
    }

    public static function icon(): string
    {
        return 'fa-server';
    }

    public static function description(): string
    {
        return __('search.sources.devices');
    }

    public static function authorized(User $user): bool
    {
        return $user->can('viewAny', Device::class);
    }

    protected function view(): string
    {
        return 'search.results.devices';
    }

    protected function baseQuery(User $user): Builder
    {
        return Device::hasAccess($user)
            ->select('devices.*')
            ->with('location')
            ->leftJoin('locations', 'devices.location_id', '=', 'locations.id')
            ->orderBy('devices.display')
            ->orderBy('devices.hostname');
    }

    protected function search(Builder $query, SearchTerm $term): Builder
    {
        return $query->where(function (Builder $query) use ($term): void {
            $like = $term->like();
            $query->where('devices.hostname', 'like', $like)
                ->orWhere('devices.sysName', 'like', $like)
                ->orWhere('devices.display', 'like', $like)
                ->orWhere('devices.hardware', 'like', $like)
                ->orWhere('devices.serial', 'like', $like)
                ->orWhere('devices.purpose', 'like', $like)
                ->orWhere('devices.notes', 'like', $like)
                ->orWhere('locations.location', 'like', $like);

            if ($term->isIp()) {
                $exact = $term->ip?->compressed() ?? '';
                $query->orWhere(fn (Builder $q) => $this->whereIp($q, 'devices.overwrite_ip', $term, $exact));

                if ($range = $term->networkRange()) {
                    $query->orWhereRaw('HEX(devices.ip) BETWEEN ? AND ?', $range);
                } elseif ($term->isExactIp()) {
                    $query->orWhere('devices.ip', $term->ip->packed());
                }

                if ($term->isIpv4()) {
                    $query->orWhereHas('ports.ipv4', fn (Builder $q) => $this->whereIp($q, 'ipv4_address', $term, $exact));
                } else {
                    $query->orWhereHas('ports.ipv6', fn (Builder $q) => $this->whereIp($q, 'ipv6_address', $term, $term->ip?->uncompressed() ?? '', 'ipv6_compressed'));
                }
            }

            if ($term->isMac()) {
                $query->orWhereHas('ports', fn (Builder $q) => $this->whereMac($q, 'ifPhysAddress', $term));
            }
        });
    }

    protected function filterDevice(Builder $query, int $deviceId): Builder
    {
        return $query->where('devices.device_id', $deviceId);
    }
}
