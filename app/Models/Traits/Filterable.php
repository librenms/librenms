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
use Illuminate\Support\Str;

trait Filterable
{
    /**
     * Comprehensive map of UI operators to Eloquent methods.
     */
    protected static array $operatorMap = [
        // Standard Comparison
        'eq' => ['method' => 'where', 'operator' => '='],
        'neq' => ['method' => 'where', 'operator' => '!='],
        'gt' => ['method' => 'where', 'operator' => '>'],
        'gte' => ['method' => 'where', 'operator' => '>='],
        'lt' => ['method' => 'where', 'operator' => '<'],
        'lte' => ['method' => 'where', 'operator' => '<='],

        // String Pattern Matching
        'contains' => ['method' => 'where', 'operator' => 'like', 'wildcard' => '%?%'],
        'not_contains' => ['method' => 'where', 'operator' => 'not like', 'wildcard' => '%?%'],
        'starts_with' => ['method' => 'where', 'operator' => 'like', 'wildcard' => '?%'],
        'ends_with' => ['method' => 'where', 'operator' => 'like', 'wildcard' => '%?'],

        // Null / Existence Checks (Nullary)
        'is_empty' => ['method' => 'whereNull'],
        'is_not_empty' => ['method' => 'whereNotNull'],

        // Set Comparisons
        'in' => ['method' => 'whereIn'],
        'not_in' => ['method' => 'whereNotIn'],
    ];

    /**
     * Apply filters from the request to the query.
     */
    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $operators) {
            if (! in_array($field, $this->filterable)) {
                continue;
            }
            if (! is_array($operators)) {
                continue;
            }

            foreach ($operators as $op => $value) {
                if (! isset(static::$operatorMap[$op])) {
                    continue;
                }

                $config = static::$operatorMap[$op];
                $method = $config['method'];

                // Check for Custom Filter Method (e.g., filterState)
                $customMethod = 'filter' . Str::studly(str_replace('.', '_', $field));

                if (method_exists($this, $customMethod)) {
                    $this->$customMethod($query, $op, $value, $config);
                    continue;
                }

                // Relational Logic (dots)
                if (str_contains((string) $field, '.')) {
                    $parts = explode('.', (string) $field);
                    $column = array_pop($parts);
                    $relation = implode('.', $parts);

                    $negated = in_array($op, ['neq', 'not_contains', 'not_in', 'is_not_empty']);
                    $hasMethod = $negated ? 'whereDoesntHave' : 'whereHas';

                    // Map negated operators to their positive equivalents for the inner clause
                    $innerOp = match ($op) {
                        'neq' => 'eq',
                        'not_contains' => 'contains',
                        'not_in' => 'in',
                        'is_not_empty' => 'is_empty',
                        default => $op,
                    };
                    $innerConfig = static::$operatorMap[$innerOp];
                    $innerMethod = $innerConfig['method'];

                    $query->{$hasMethod}($relation, function (Builder $q) use ($column, $innerOp, $value, $innerConfig, $innerMethod): void {
                        $this->applyQueryLogic($q, $column, $innerOp, $value, $innerConfig, $innerMethod);
                    });
                } else {
                    // Standard Local Logic
                    $this->applyQueryLogic($query, $this->qualifyColumn($field), $op, $value, $config, $method);
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
        $isNegation = in_array($op, ['neq', 'not_contains', 'is_not_empty']);

        $query->where(function (Builder $q) use ($columns, $op, $value, $config, $isNegation) {
            foreach ($columns as $field) {
                $boolean = $isNegation ? 'and' : 'or';

                if (str_contains($field, '.')) {
                    $parts = explode('.', $field);
                    $column = array_pop($parts);
                    $relation = implode('.', $parts);
                    $hasMethod = $isNegation ? 'whereDoesntHave' : 'whereHas';

                    $q->{$hasMethod}($relation, fn (Builder $r) => $this->applyQueryLogic($r, $column, $op, $value, $config, $config['method']), boolean: $boolean);
                } else {
                    $this->applyQueryLogic($q, $this->qualifyColumn($field), $op, $value, $config, $config['method'], $boolean);
                }
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

    protected function applyQueryLogic($query, $field, $op, $value, $config, $method): void
    {
        if (in_array($op, ['is_empty', 'is_not_empty'])) {
            $query->where(function (Builder $q) use ($field, $op) {
                $op === 'is_not_empty'
                    ? $q->whereNotNull($field)->where($field, '!=', '')
                    : $q->whereNull($field)->orWhere($field, '=', '');
            });

            return;
        }

        if (in_array($op, ['in', 'not_in'])) {
            $values = is_array($value) ? $value : explode(',', (string) $value);
            $query->$method($field, $values);

            return;
        }

        if (isset($config['wildcard'])) {
            $value = str_replace('?', $value, $config['wildcard']);
        }

        if (isset($config['operator'])) {
            $query->$method($field, $config['operator'], $value);
        } else {
            $query->$method($field, $value);
        }
    }
}
