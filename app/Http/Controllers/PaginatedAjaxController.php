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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * @template TModel of Model
 */
abstract class PaginatedAjaxController extends Controller
{
    /**
     * Default sort, column => direction
     */
    protected array $default_sort = [];

    /**
     * Base rules for this controller.
     *
     * @return mixed
     */
    abstract protected function baseRules();

    /**
     * Defines the base query for this resource
     *
     * @param  Request  $request
     * @return Builder<TModel>|\Illuminate\Database\Query\Builder
     */
    abstract protected function baseQuery(Request $request): Builder|\Illuminate\Database\Query\Builder;

    /**
     * @param  Paginator  $paginator
     */
    abstract protected function formatResponse($paginator): JsonResponse;

    /**
     * Defines validation rules (will override base validation rules for select2 responses too)
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * Defines search fields. They will be searched in order.
     */
    protected function searchFields(Request $request): array
    {
        return [];
    }

    /**
     * Defines filter fields.  Request and table fields must match.
     */
    protected function filterFields(Request $request): array
    {
        return [];
    }

    /**
     * Defines sortable fields.  The incoming sort field should be the key, the sql column or DB::raw() should be the value
     */
    protected function sortFields(Request $request): array
    {
        return [];
    }

    /**
     * Format an item for display.  Default is pass-through
     *
     * @param  TModel  $model
     * @return array<string, scalar>|Collection<string, scalar>|TModel
     */
    public function formatItem(Model $model): Model|array|Collection
    {
        return $model;
    }

    protected function search(?string $search, Builder $query, array $fields): Builder
    {
        if ($search) {
            $query->where(function (Builder $query) use ($fields, $search): void {
                foreach ($fields as $index => $field) {
                    if (! is_numeric($index)) {
                        $query->orWhereHas($index, function ($query) use ($field, $search): void {
                            $query->where(function ($query) use ($field, $search): void {
                                foreach ($field as $relatedField) {
                                    $query->orWhere($relatedField, 'like', '%' . $search . '%');
                                }
                            });
                        });
                    } else {
                        $query->orWhere($field, 'like', '%' . $search . '%');
                    }
                }
            });
        }

        return $query;
    }

    protected function filter(Request $request, Builder $query, array $fields): Builder
    {
        foreach ($fields as $target => $field) {
            $callable = is_callable($field);
            $value = $request->input($callable ? $target : $field);

            // unfiltered field
            if ($value === null) {
                continue;
            }

            // apply the filter
            if ($callable) {
                $field($query, $value);
            } else {
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

    protected function sort(Request $request, Builder $query): Builder
    {
        $columns = $this->sortFields($request);

        $sort = $request->input('sort', $this->default_sort);

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
     */
    public function validate(Request $request, array $rules = [], array $messages = [], array $attributes = []): array
    {
        $full_rules = array_replace($this->baseRules(), $rules);

        return parent::validate($request, $full_rules, $messages, $attributes);
    }

    /**
     * Sometimes filter values need to be modified to work
     * For example if the filter value is a string, when it needs to be an id
     */
    protected function adjustFilterValue(string $field, mixed $value): mixed
    {
        return match ($field) {
            'device', 'device_id', 'port_id' => (int) $value,
            default => $value,
        };
    }
}
