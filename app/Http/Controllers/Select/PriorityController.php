<?php
/**
 * PriorityController.php
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
 */

namespace App\Http\Controllers\Select;

use App\Models\Syslog;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class PriorityController extends SelectController
{
    /**
     * The default method called by the route handler
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $this->validate($request, $this->rules());
        $limit = $request->get('limit', 50);
        $levels = app('translator')->get('syslog.severity');
        $items = array_map(function ($id, $name) {
            return ['id' => $id, 'name' => $name];
        }, array_keys($levels), array_values($levels));

        $paginator = new Paginator($items, $limit, 0);

        return $this->formatResponse($paginator);
    }

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseQuery($request)
    {
        return Syslog::query();
    }

    public function formatItem($item)
    {
        return [
            'id' => $item['id'],
            'text' => $item['name'],
        ];
    }
}
