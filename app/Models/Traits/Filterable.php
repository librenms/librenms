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
    protected array $operatorMap = [
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
        $allowed = $this->filterable ?? [];

        foreach ($filters as $field => $operators) {
            if (!in_array($field, $allowed)) continue;
            if (!is_array($operators)) continue;

            foreach ($operators as $op => $value) {
                if (!isset($this->operatorMap[$op])) continue;

                $config = $this->operatorMap[$op];
                $method = $config['method'];

                // Check for Custom Filter Method (e.g., filterState)
                $customMethod = 'filter' . Str::studly(str_replace('.', '_', $field));

                if (method_exists($this, $customMethod)) {
                    $this->$customMethod($query, $op, $value, $config);
                    continue;
                }

                // Relational Logic (dots)
                if (str_contains($field, '.')) {
                    $parts = explode('.', $field);
                    $column = array_pop($parts);
                    $relation = implode('.', $parts);

                    $query->whereHas($relation, function (Builder $q) use ($column, $op, $value, $config, $method) {
                        $this->applyQueryLogic($q, $column, $op, $value, $config, $method);
                    });
                } else {
                    // Standard Local Logic
                    $this->applyQueryLogic($query, $this->qualifyColumn($field), $op, $value, $config, $method);
                }
            }
        }

        return $query;
    }

    protected function applyQueryLogic($query, $field, $op, $value, $config, $method): void
    {
        if (in_array($op, ['is_empty', 'is_not_empty'])) {
            $query->$method($field);
            return;
        }

        if (in_array($op, ['in', 'not_in'])) {
            $values = is_array($value) ? $value : explode(',', $value);
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
