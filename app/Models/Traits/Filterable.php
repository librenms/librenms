<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait Filterable
{
    /**
     * @var array<string, array{operator?: string, wildcard?: string, not?: bool, null?: bool, set?: bool}>
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
     *
     * @param  Builder<static>  $query
     * @param  array<string, array<string, mixed>>  $filters
     * @return Builder<static>
     */
    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $operators) {
            if (! in_array($field, $this->filterable)) {
                Log::debug("Field '$field' not filterable in " . $this::class);

                continue;
            }
            if (! is_array($operators)) {
                Log::debug('Invalid operators format ' . $this::class);

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
                    $this->$customMethod($query, $value, $config);
                    continue;
                }

                if (str_contains((string) $field, '.')) {
                    $this->applyRelationFilter($query, $field, $value, $config);
                } else {
                    $this->applyQueryLogic($query, $this->qualifyColumn($field), $value, $config);
                }
            }
        }

        return $query;
    }

    /**
     * @param  array  $columns
     * @param  Builder<static>  $query
     * @param  scalar|scalar[]  $value
     * @param  array{operator?: string, wildcard?: string, not?: bool, null?: bool, set?: bool}  $config
     * @return void
     */
    protected function applyFilterSearch(array $columns, Builder $query, mixed $value, array $config): void
    {
        $not = $config['not'] ?? false;

        $query->where(function (Builder $q) use ($columns, $value, $config, $not): void {
            foreach ($columns as $field) {
                $boolean = $not ? 'and' : 'or';

                str_contains($field, '.')
                    ? $this->applyRelationFilter($q, $field, $value, $config, $boolean)
                    : $this->applyQueryLogic($q, $this->qualifyColumn($field), $value, $config, $boolean);
            }
        });
    }

    /**
     * Helper to build complex filters based on a value-to-logic mapping.
     *
     * @param  Builder<static>  $query
     * @param  scalar|scalar[]  $value
     * @param  array{operator?: string, wildcard?: string, not?: bool, null?: bool, set?: bool}  $config
     * @param  callable  $mapper  Accepts value and modifies the query
     */
    protected function applyMappedFilter(Builder $query, mixed $value, array $config, callable $mapper): void
    {
        $values = is_array($value) ? $value : explode(',', (string) $value);
        $not = $config['not'] ?? false;

        $query->{$not ? 'whereNot' : 'where'}(function (Builder $group) use ($values, $mapper): void {
            foreach ($values as $v) {
                $group->orWhere(fn (Builder $q) => $mapper($q, $v));
            }
        });
    }

    /**
     * @param  Builder<static>  $query
     * @param  string  $field
     * @param  scalar|scalar[]  $value
     * @param  array{operator?: string, wildcard?: string, not?: bool, null?: bool, set?: bool}  $config
     * @param  'and'|'or'  $boolean
     * @return void
     */
    protected function applyQueryLogic(Builder $query, string $field, mixed $value, array $config, string $boolean = 'and'): void
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

        $not ? $query->whereNot($field, $config['operator'], $value, $boolean)
            : $query->where($field, $config['operator'], $value, $boolean);
    }

    /**
     * @param  Builder<static>  $query
     * @param  string  $field
     * @param  scalar|scalar[]  $value
     * @param  array{operator?: string, wildcard?: string, not?: bool, null?: bool, set?: bool}  $config
     * @param  'and'|'or'  $boolean
     * @return void
     */
    protected function applyRelationFilter(Builder $query, string $field, mixed $value, array $config, string $boolean = 'and'): void
    {
        $parts = explode('.', $field);
        $column = array_pop($parts);
        $relation = implode('.', $parts);

        $not = $config['not'] ?? false;

        // Callback to apply the logic inside the relationship scope
        $callback = function (Builder $q) use ($column, $value, $config): void {
            // When negating a relation (whereDoesntHave), we must use "positive" internal logic
            $internalConfig = $config;
            $internalConfig['not'] = false;
            $this->applyQueryLogic($q, $column, $value, $internalConfig);
        };

        if ($not) {
            $boolean === 'or'
                ? $query->orWhereDoesntHave($relation, $callback)
                : $query->whereDoesntHave($relation, $callback);
        } else {
            $boolean === 'or'
                ? $query->orWhereHas($relation, $callback)
                : $query->whereHas($relation, $callback);
        }
    }

    public static function filterValidationRules(): array
    {
        return [
            'filter' => ['array'],
            'filter.*' => [
                'array',
                function ($attribute, $value, $fail): void {
                    $operator = array_key_first($value);
                    if (! isset(static::$operatorMap[$operator])) {
                        $fail("The operator '$operator' is not supported.");
                    }
                },
            ],
            'filter.*.*' => ['nullable', 'max:255'],
        ];
    }
}
