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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Device;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @extends TableController<Location>
 */
class LocationController extends TableController
{
    /**
     * Defines search fields will be searched in order
     */
    public function searchFields(Request $request): array
    {
        return ['location'];
    }

    protected function sortFields(Request $request): array
    {
        return [
            'location',
            'devices' => 'devices_count',
            'down' => 'down_count',
        ];
    }

    /**
     * Defines the base query for this resource
     */
    public function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Location::class);

        return Location::hasAccess($request->user())->withCount([
            'devices',
            'devices as down_count' => fn ($q) => (new Device)->scopeIsDown($q),
        ]);
    }

    /**
     * @param  Location  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        return [
            'id' => $model->id,
            'location' => $model->location,
            'lat' => $model->lat,
            'lng' => $model->lng,
            'devices' => $model->devices_count,
            /** @phpstan-ignore property.notFound (dynamic property from withCount) */
            'down' => $model->down_count,
        ];
    }
}
