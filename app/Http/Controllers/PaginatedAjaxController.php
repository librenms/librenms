<?php
/**
 * AjaxController.php
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

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class PaginatedAjaxController extends Controller
{
    /**
     * Default sort, column => direction
     * @var array
     */
    protected $default_sort = [];

    /**
     * Base rules for this controller.
     *
     * @return mixed
     */
    abstract protected function baseRules();

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    abstract protected function baseQuery($request);

    /**
     * @param Paginator $paginator
     * @return \Illuminate\Http\JsonResponse
     */
    abstract protected function formatResponse($paginator);

    /**
     * Defines validation rules (will override base validation rules for select2 responses too)
     *
     * @return array
     */
    protected function rules()
    {
        return [];
    }

    /**
     * Defines search fields. They will be searched in order.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function searchFields($request)
    {
        return [];
    }

    /**
     * Defines filter fields.  Request and table fields must match.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function filterFields($request)
    {
        return [];
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
        return [];
    }

    /**
     * Format an item for display.  Default is pass-through
     *
     * @param Model $model
     * @return array|Collection|Model
     */
    public function formatItem($model)
    {
        return $model;
    }

    /**
     * @param string $search
     * @param Builder $query
     * @param array $fields
     * @return Builder
     */
    protected function search($search, $query, $fields)
    {
        if ($search) {
            $query->where(function ($query) use ($fields, $search) {
                foreach ($fields as $field) {
                    $query->orWhere($field, 'like', '%' . $search . '%');
                }
            });
        }

        return $query;
    }

    /**
     * @param Request $request
     * @param Builder $query
     * @param array $fields
     * @return Builder
     */
    protected function filter($request, $query, $fields)
    {
        foreach ($fields as $target => $field) {
            if ($value = $request->get($field)) {
                $value = $this->adjustFilterValue($field, $value);
                if (is_string($target)) {
                    $query->where($target, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        return $query;
    }

    /**
     * @param Request $request
     * @param Builder $query
     * @return Builder
     */
    protected function sort($request, $query)
    {
        $columns = $this->sortFields($request);

        $sort = $request->get('sort', $this->default_sort);

        foreach ($sort as $column => $direction) {
            if (isset($columns[$column]) || in_array($column, $columns)) {
                $name = $columns[$column] ?? $column;
                $query->orderBy($name, $direction == 'desc' ? 'desc' : 'asc');
            }
        }

        return $query;
    }

    /**
     * Validate the given request with the given rules.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  array $rules
     * @param  array $messages
     * @param  array $customAttributes
     * @return array
     */
    public function validate(Request $request, array $rules = [], array $messages = [], array $customAttributes = [])
    {
        $full_rules = array_replace($this->baseRules(), $rules);

        return parent::validate($request, $full_rules, $messages, $customAttributes);
    }

    /**
     * Sometimes filter values need to be modified to work
     * For example if the filter value is a string, when it needs to be an id
     *
     * @param string $field The field being filtered
     * @param mixed $value The current value
     * @return mixed
     */
    protected function adjustFilterValue($field, $value)
    {
        switch ($field) {
            case 'device':
            case 'device_id':
            case 'port_id':
                $value = (int) $value;
                break;
        }

        return $value;
    }
}
