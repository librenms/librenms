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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
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
    protected $model;

    protected function sortFields($request)
    {
        if (isset($this->model)) {
            $fields = \Schema::getColumnListing((new $this->model)->getTable());

            return array_combine($fields, $fields);
        }

        return [];
    }

    final protected function baseRules()
    {
        return SimpleTableController::$base_rules;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $this->validate($request, $this->rules());

        /** @var Builder $query */
        $query = $this->baseQuery($request);

        $this->search($request->get('searchPhrase'), $query, $this->searchFields($request));
        $this->filter($request, $query, $this->filterFields($request));
        $this->sort($request, $query);

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
