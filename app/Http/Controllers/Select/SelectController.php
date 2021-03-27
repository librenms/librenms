<?php
/**
 * SelectController.php
 *
 * Controller class for select2 ajax controllers.
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

use App\Http\Controllers\PaginatedAjaxController;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class SelectController extends PaginatedAjaxController
{
    final protected function baseRules()
    {
        return [
            'limit' => 'int',
            'page' => 'int',
            'term' => 'nullable|string',
        ];
    }

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

        $query = $this->search($request->get('term'), $this->baseQuery($request), $this->searchFields($request));
        $this->sort($request, $query);
        $paginator = $query->simplePaginate($limit);

        return $this->formatResponse($paginator);
    }

    /**
     * @param Paginator $paginator
     * @return \Illuminate\Http\JsonResponse
     */
    protected function formatResponse($paginator)
    {
        return response()->json([
            'results' => collect($paginator->items())->map([$this, 'formatItem']),
            'pagination' => ['more' => $paginator->hasMorePages()],
        ]);
    }

    /**
     * Default item formatting, should supply at least id and text keys
     * Check select2 docs.
     * Default implementation uses primary key and the first value in the model
     * If only one value is in the model attributes, that is the id and text.
     *
     * @param Model $model
     * @return array
     */
    public function formatItem($model)
    {
        $attributes = collect($model->getAttributes());

        return [
            'id' => $attributes->count() == 1 ? $attributes->first() : $model->getKey(),
            'text' => $attributes->forget($model->getKeyName())->first(),
        ];
    }
}
