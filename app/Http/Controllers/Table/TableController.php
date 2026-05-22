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
use Countable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @template TModel of Model
 *
 * @extends PaginatedAjaxController<TModel>
 */
abstract class TableController extends PaginatedAjaxController
{
    /** @var class-string|null The model class to use */
    protected ?string $model = null;

    protected function sortFields(Request $request): array
    {
        if (isset($this->model)) {
            $fields = \Schema::getColumnListing((new $this->model)->getTable());

            return array_combine($fields, $fields);
        }

        return [];
    }

    final protected function baseRules(): array
    {
        return SimpleTableController::$base_rules;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->validate($request, $this->rules());

        /** @var Builder $query */
        $query = $this->baseQuery($request);

        $this->filter($request, $query, $this->filterFields($request));
        $this->search($request->input('searchPhrase'), $query, $this->searchFields($request));
        $this->sort($request, $query);

        $limit = $request->input('rowCount', 25);
        $page = $request->input('current', 1);
        if ($limit < 0) {
            $limit = $query->count();
            $page = null;
        }
        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        return $this->formatResponse($paginator);
    }

    /**
     * @param  LengthAwarePaginator|Countable  $paginator
     */
    protected function formatResponse($paginator): JsonResponse
    {
        return response()->json([
            'current' => $paginator->currentPage(),
            'rowCount' => $paginator->count(),
            'rows' => collect($paginator->items())->map($this->formatItem(...)),
            'total' => $paginator->total(),
        ]);
    }

    /**
     * Export data as CSV
     */
    public function export(Request $request): StreamedResponse
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
     */
    protected function prepareExportQuery(Request $request): Builder
    {
        $query = $this->baseQuery($request);

        $this->filter($request, $query, $this->filterFields($request));

        if ($request->has('searchPhrase') && ! empty($request->input('searchPhrase'))) {
            $this->search($request->input('searchPhrase'), $query, $this->searchFields($request));
        }

        if ($request->has('current') && $request->has('rowCount')) {
            $limit = $request->input('rowCount');
            $page = $request->input('current');

            if ($limit > 0) {
                $offset = ($page - 1) * $limit;
                $query->skip($offset)->take($limit);
            }
        }

        return $query;
    }

    /**
     * Get headers for CSV export
     */
    protected function getExportHeaders(): array
    {
        return $this->visibleColumns();
    }

    /**
     * Get the visible columns for this table
     */
    protected function visibleColumns(): array
    {
        if (isset($this->model)) {
            $fields = \Schema::getColumnListing((new $this->model)->getTable());

            // Convert DB column names to human-readable format
            return array_map(fn ($field) => ucwords(str_replace('_', ' ', $field)), $fields);
        }

        return [];
    }

    /**
     * Format a row for CSV export
     *
     * @param  TModel  $item
     * @return array<scalar>
     */
    protected function formatExportRow(Model $item): array
    {
        // First try using formatItem if it exists
        $formatted = $this->formatItem($item);

        // If formatItem returns an array, process it to remove HTML
        if (is_array($formatted)) {
            return array_map(fn ($value) => is_string($value) ? trim(strip_tags($value)) : $value, $formatted);
        }

        return $item->toArray();
    }

    /**
     * Generate CSV response from data
     *
     * @param  Collection<TModel>  $data
     */
    protected function generateCsvResponse(Collection $data, array $headers, string $filename): StreamedResponse
    {
        return response()->stream(
            function () use ($data, $headers): void {
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
