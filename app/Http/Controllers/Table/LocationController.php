<?php
/**
 * LocationsController.php
 *
 * -Description-
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Device;
use App\Models\Location;

class LocationController extends TableController
{
    /**
     * Defines search fields will be searched in order
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function searchFields($request)
    {
        return ['location'];
    }

    protected function sortFields($request)
    {
        return ['location', 'devices', 'network', 'servers', 'firewalls', 'down'];
    }

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function baseQuery($request)
    {
        // joins are needed for device count sorts
        $sort = $request->get('sort');
        $key = key($sort);
        $join = $this->getJoinQuery($key);

        if ($join) {
            return Location::hasAccess($request->user())
                ->select(['id', 'location', 'lat', 'lng', \DB::raw("COUNT(device_id) AS `$key`")])
                ->leftJoin('devices', $join)
                ->groupBy(['id', 'location', 'lat', 'lng']);
        }

        return Location::hasAccess($request->user());
    }

    /**
     * @param Location $location
     * @return array|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection
     */
    public function formatItem($location)
    {
        return [
            'id' => $location->id,
            'location' => $location->location,
            'lat' => $location->lat,
            'lng' => $location->lng,
            'down' => $location->devices()->isDown()->count(),
            'devices' => $location->devices()->count(),
            'network' => $location->devices()->where('type', 'network')->count(),
            'servers' => $location->devices()->where('type', 'server')->count(),
            'firewalls' => $location->devices()->where('type', 'firewall')->count(),
        ];
    }

    private function getJoinQuery($field)
    {
        switch ($field) {
            case 'devices':
                return function ($query) {
                    $query->on('devices.location_id', 'locations.id');
                };
            case 'down':
                return function ($query) {
                    $query->on('devices.location_id', 'locations.id');
                    (new Device)->scopeIsDown($query);
                };
            case 'network':
                return function ($query) {
                    $query->on('devices.location_id', 'locations.id')
                        ->where('devices.type', 'network');
                };
            case 'servers':
                return function ($query) {
                    $query->on('devices.location_id', 'locations.id')
                        ->where('devices.type', 'server');
                };
            case 'firewalls':
                return function ($query) {
                    $query->on('devices.location_id', 'locations.id')
                        ->where('devices.type', 'firewall');
                };
            default:
                return null;
        }
    }
}
