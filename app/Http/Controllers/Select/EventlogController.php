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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\Models\Eventlog;

class EventlogController extends SelectController
{
    protected $default_sort = ['type' => 'asc'];

    /**
     * Defines validation rules (will override base validation rules for select2 responses too)
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'field' => 'required|in:type',
            'device' => 'nullable|int',
        ];
    }

    /**
     * Defines sortable fields.  The incoming sort field should be the key, the sql column or DB::raw() should be the value
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function sortFields($request)
    {
        return ['type'];
    }

    /**
     * Defines search fields will be searched in order
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function searchFields($request)
    {
        return [$request->get('field')];
    }

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseQuery($request)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = Eventlog::hasAccess($request->user())
            ->select($request->get('field'))->distinct();

        if ($device_id = $request->get('device')) {
            $query->where('device_id', $device_id);
        }

        return $query;
    }
}
