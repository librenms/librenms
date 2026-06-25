<?php

/**
 * EventlogController.php
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

namespace App\Http\Controllers\Select;

use App\Models\Eventlog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * @extends SelectController<Eventlog>
 */
class EventlogController extends SelectController
{
    protected array $default_sort = ['type' => 'asc'];

    /**
     * Defines validation rules (will override base validation rules for select2 responses too)
     */
    protected function rules(): array
    {
        return [
            'field' => 'required|in:type',
            'device' => 'nullable|int',
        ];
    }

    /**
     * Defines sortable fields.  The incoming sort field should be the key, the sql column or DB::raw() should be the value
     */
    protected function sortFields(Request $request): array
    {
        return ['type'];
    }

    /**
     * Defines search fields will be searched in order
     */
    protected function searchFields(Request $request): array
    {
        return [$request->input('field')];
    }

    /**
     * Defines the base query for this resource
     */
    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Eventlog::class);

        $query = Eventlog::hasAccess($request->user())
            ->select($request->input('field'))->distinct();

        if ($device_id = $request->input('device')) {
            $query->where('device_id', $device_id);
        }

        return $query;
    }
}
