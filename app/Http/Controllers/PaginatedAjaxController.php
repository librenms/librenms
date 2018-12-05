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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
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
    abstract public function baseQuery($request);

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
    public function rules()
    {
        return [];
    }

    /**
     * Defines search fields will be searched in order
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function searchFields($request)
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
     * @param string
     * @param Builder $query
     * @param array $fields
     * @return Builder
     */
    protected function search($search, $query, $fields)
    {
        if ($search) {
            $query->where(function ($query) use ($fields, $search) {
                /** @var Builder $query */
                foreach ($fields as $field) {
                    $query->orWhere($field, 'like', '%' . $search . '%');
                }
            });
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
     * @return void
     */
    public function validate(Request $request, array $rules = [], array $messages = [], array $customAttributes = [])
    {
        $full_rules = array_replace($this->baseRules(), $rules);

        parent::validate($request, $full_rules, $messages, $customAttributes);
    }
}
