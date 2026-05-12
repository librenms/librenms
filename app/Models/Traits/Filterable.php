<?php

/**
 * Filterable.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait Filterable
{
    /**
     * Comprehensive map of UI operators to Eloquent methods.
     */
    protected static array $operatorMap = [
        // Standard Comparison
        'eq' => ['operator' => '='],
        'neq' => ['operator' => '=', 'not' => true],
        'gt' => ['operator' => '>'],
        'gte' => ['operator' => '>='],
        'lt' => ['operator' => '<'],
        'lte' => ['operator' => '<='],

        // String Pattern Matching
        'contains' => ['operator' => 'like', 'wildcard' => '%?%'],
        'not_contains' => ['operator' => 'like', 'wildcard' => '%?%', 'not' => true],
        'starts_with' => ['operator' => 'like', 'wildcard' => '?%'],
        'ends_with' => ['operator' => 'like', 'wildcard' => '%?'],

        // Null / Existence Checks
        'is_empty' => ['null' => true],
        'is_not_empty' => ['null' => true, 'not' => true],

        // Set Comparisons
        'in' => ['set' => true],
        'not_in' => ['set' => true, 'not' => true],
    ];

    /**
     * Apply filters from the request to the query.
     */
    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $operators) {
            if (! in_array($field, $this->filterable)) {
                Log::debug("Field '$field' not filterable in " . $this::class);

                continue;
            }
            if (! is_array($operators)) {
                Log::debug("Invalid operators format " . $this::class);

                continue;
            }

            foreach ($operators as $op => $value) {
                if (! isset(static::$operatorMap[$op])) {
                    Log::debug("Invalid filter operator '$op' in " . $this::class);

                    continue;
                }

                $config = static::$operatorMap[$op];

                // Check for Custom Filter Method (e.g., filterState)
                $customMethod = 'filter' . Str::studly(str_replace('.', '_', $field));
                if (method_exists($this, $customMethod)) {
                    $this->$customMethod($query, $op, $value, $config);
                    continue;
                }

                if (str_contains((string) $field, '.')) {
                    $this->applyRelationFilter($query, $field, $op, $value, $config);
                } else {
                    $this->applyQueryLogic($query, $this->qualifyColumn($field), $op, $value, $config);
                }
            }
        }

        return $query;
    }

    /**
     * Helper for models to build multi-column search filters.
     * Call this from a custom filterSearch() (or any filterX()) method on the model.
     */
    protected function applyFilterSearch(array $columns, Builder $query, string $op, mixed $value, array $config): void
    {
        $not = $config['not'] ?? false;

        $query->where(function (Builder $q) use ($columns, $op, $value, $config, $not): void {
            foreach ($columns as $field) {
                $boolean = $not ? 'and' : 'or';
                str_contains($field, '.')
                    ? $this->applyRelationFilter($q, $field, $op, $value, $config, $boolean)
                    : $this->applyQueryLogic($q, $this->qualifyColumn($field), $op, $value, $config, $boolean);
            }
        });
    }

    public static function filterValidationRules(): array
    {
        return [
            'filter' => ['array'],
            'filter.*' => [
                'array',
                function ($attribute, $value, $fail): void {
                    $operator = array_key_first($value);
                    if (! in_array($operator, array_keys(static::$operatorMap))) {
                        $fail("The operator '$operator' is not supported.");
                    }
                },
            ],
            'filter.*.*' => ['nullable', 'max:255'],
        ];
    }

    protected function applyQueryLogic(Builder $query, string $field, string $op, mixed $value, array $config, string $boolean = 'and'): void
    {
        $not = $config['not'] ?? false;

        if ($config['null'] ?? false) {
            $query->where(function (Builder $q) use ($field, $not): void {
                $not
                    ? $q->whereNotNull($field)->where($field, '!=', '')
                    : $q->whereNull($field)->orWhere($field, '=', '');
            }, boolean: $boolean);
            return;
        }

        if ($config['set'] ?? false) {
            $values = is_array($value) ? $value : explode(',', (string) $value);
            $query->whereIn($field, $values, $boolean, $not);
            return;
        }

        if (isset($config['wildcard'])) {
            $value = str_replace('?', $value, $config['wildcard']);
        }

        $query->where($field, $config['operator'], $value, $boolean);
    }

    protected function applyRelationFilter(Builder $query, string $field, string $op, mixed $value, array $config, string $boolean = 'and'): void
    {
        $parts = explode('.', $field);
        $column = array_pop($parts);
        $relation = implode('.', $parts);

        $not = in_array($op, ['is_not_empty', 'not_in']);

        $query->whereHas($relation, fn (Builder $q) => $this->applyQueryLogic($q, $column, $op, $value, $config), $boolean, $not);
    }
}
