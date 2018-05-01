<?php
/**
 * BaseList.php
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

namespace App\Http\Selects;


use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class BaseSelect
{
    protected $request;
    protected $limit;
    protected $offset;
    protected $search;
    protected $total = 0;

    protected $searchFields = [];
    protected $selectFields = '*';

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->limit = $request->get('limit', 0);
        $this->offset = ($request->get('page', 1) - 1) * $this->limit;
        $this->search = $request->get('search');
    }

    /**
     * Get the base query for this object
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    abstract protected function baseQuery();

    /**
     * Override this to format the data as needed
     *
     * @param Collection $items
     * @return Collection
     */
    protected function format($items)
    {
        return $items;
    }

    /**
     * Main worker function
     *
     * @return array
     */
    public function get()
    {
        $items = $this->load();

        return $this->buildJson($items);
    }

    /**
     * Get collection of paginated items
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Collection
     */
    protected function load()
    {
        $query = $this->baseQuery();

        $this->search($query);
        $this->paginate($query);

        return $query->select($this->selectFields)->get();
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    protected function search($query)
    {
        $fields = $this->searchFields;
        if ($this->search) {
            $query->where(function($query) use ($fields) {
                foreach ($fields as $field) {
                    $query->orWhere($field, 'like', "%{$this->search}%");
                }
            });
        }

        return $query;
    }

    /**
     * @param Collection $results The results array as expected by select2 should have id and name keys
     * @param bool $more if there are more items available
     * @return array
     */
    protected function buildJson($results, $more = null)
    {
        if (is_null($more)) {
            $more = $this->hasMore($results);
        }

        return [
            'results' => $this->format($results)->toArray(),
            'pagination' => ['more' => $more]
        ];
    }

    /**
     * Apply the limit and offset to the query if appropriate
     *
     * @param Builder $query
     * @return Builder
     */
    protected function paginate($query)
    {
        $this->total = $query->count();

        if ($this->limit) {
            $query->limit($this->limit)->offset($this->offset);
        }

        return $query;
    }

    /**
     * Check if there are more pages after the current returned page
     *
     * @param Collection $items
     * @return bool
     */
    protected function hasMore($items)
    {
        return ($this->offset + $items->count()) < $this->total;
    }
}
