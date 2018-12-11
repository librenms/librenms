<?php
/**
 * TableController.php
 *
 * Controller class for bootgrid ajax controllers.
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Http\Controllers\PaginatedAjaxController;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class TableController extends PaginatedAjaxController
{
    final protected function baseRules()
    {
        return [
            'current' => 'int',
            'rowCount' => 'int',
            'searchPhrase' => 'nullable|string',
            'sort.*' => 'in:asc,desc',
        ];
    }

    public function __invoke(Request $request)
    {
        $this->validate($request, $this->rules());

        /** @var Builder $query */
        $query = $this->baseQuery($request);

        $this->search($request->get('searchPhrase'), $query, $this->searchFields($request));

        $sort = $request->get('sort', []);
        foreach ($sort as $column => $direction) {
            $query->orderBy($column, $direction);
        }

        $limit = $request->get('rowCount', 25);
        $page = $request->get('current', 1);
        if ($limit < 0) {
            $limit = $query->count();
            $page = null;
        }
        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        return $this->formatResponse($paginator);
    }

    /**
     * @param LengthAwarePaginator $paginator
     * @return \Illuminate\Http\JsonResponse
     */
    protected function formatResponse($paginator)
    {
        return response()->json([
            'current' => $paginator->currentPage(),
            'rowCount' => $paginator->count(),
            'rows' => collect($paginator->items())->map([$this, 'formatItem']),
            'total' => $paginator->total(),
        ]);
    }
}
