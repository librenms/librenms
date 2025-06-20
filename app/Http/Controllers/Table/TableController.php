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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Http\Controllers\PaginatedAjaxController;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $this->validate($request, $this->rules());

        /** @var Builder $query */
        $query = $this->baseQuery($request);

        $this->filter($request, $query, $this->filterFields($request));
        $this->search($request->get('searchPhrase'), $query, $this->searchFields($request));
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
     * @param  LengthAwarePaginator|\Countable  $paginator
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

    /**
     * Export data as CSV
     *
     * @param  Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $query = $this->prepareExportQuery($request);

        if ($request->has('sort')) {
            $this->sort($request, $query);
        }

        $data = $query->get();

        // Check if model property is set and is a valid class
        if (isset($this->model) && class_exists($this->model)) {
            $table = (new $this->model)->getTable();
        } else {
            // Fallback to a default table name or derive from class name
            $className = class_basename($this);
            $table = Str::snake(str_replace('TableController', '', $className));
        }
        $filename = $table . '-' . date('Y-m-d-His') . '.csv';

        $headers = $this->getExportHeaders();

        return $this->generateCsvResponse($data, $headers, $filename);
    }

    /**
     * Prepare the query for export with all filters applied
     *
     * @param  Request  $request
     * @return Builder
     */
    protected function prepareExportQuery(Request $request)
    {
        $query = $this->baseQuery($request);

        $this->filter($request, $query, $this->filterFields($request));

        if ($request->has('searchPhrase') && ! empty($request->get('searchPhrase'))) {
            $this->search($request->get('searchPhrase'), $query, $this->searchFields($request));
        }

        if ($request->has('current') && $request->has('rowCount')) {
            $limit = $request->get('rowCount');
            $page = $request->get('current');

            if ($limit > 0) {
                $offset = ($page - 1) * $limit;
                $query->skip($offset)->take($limit);
            }
        }

        return $query;
    }

    /**
     * Get headers for CSV export
     *
     * @return array
     */
    protected function getExportHeaders()
    {
        return $this->visibleColumns();
    }

    /**
     * Get the visible columns for this table
     *
     * @return array
     */
    protected function visibleColumns()
    {
        if (isset($this->model)) {
            $fields = \Schema::getColumnListing((new $this->model)->getTable());

            // Convert DB column names to human-readable format
            return array_map(function ($field) {
                return ucwords(str_replace('_', ' ', $field));
            }, $fields);
        }

        return [];
    }

    /**
     * Format a row for CSV export
     *
     * @param  mixed  $item
     * @return array
     */
    protected function formatExportRow($item)
    {
        // First try using formatItem if it exists
        $formatted = $this->formatItem($item);

        // If formatItem returns an array, process it to remove HTML
        if (is_array($formatted)) {
            return array_map(function ($value) {
                return is_string($value) ? trim(strip_tags($value)) : $value;
            }, $formatted);
        }

        if (method_exists($item, 'toArray')) {
            return $item->toArray();
        }

        return (array) $item;
    }

    /**
     * Generate CSV response from data
     *
     * @param  \Illuminate\Support\Collection  $data
     * @param  array  $headers
     * @param  string  $filename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    protected function generateCsvResponse($data, $headers, $filename)
    {
        return response()->stream(
            function () use ($data, $headers) {
                $output = fopen('php://output', 'w');

                fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($output, $headers);

                foreach ($data as $item) {
                    $row = $this->formatExportRow($item);
                    fputcsv($output, $row);
                }

                fclose($output);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ]
        );
    }
}
