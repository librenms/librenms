<?php
/**
 * QueryBuilderFluentParser.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Alerting;

use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use Log;

class QueryBuilderFluentParser extends QueryBuilderParser
{
    /**
     * Convert the query builder rules to a Laravel Fluent builder
     *
     * @return Builder|null
     */
    public function toQuery()
    {
        if (empty($this->builder) || ! array_key_exists('condition', $this->builder)) {
            return null;
        }

        $query = DB::table('devices');

        $this->joinTables($query);

        $this->parseGroupToQuery($query, $this->builder);

        return $query;
    }

    /**
     * @param Builder $query
     * @param array $rule
     * @param string $parent_condition AND or OR  (for root, this should be null)
     * @return Builder
     */
    protected function parseGroupToQuery($query, $rule, $parent_condition = null)
    {
        return $query->where(function ($query) use ($rule) {
            foreach ($rule['rules'] as $group_rule) {
                if (array_key_exists('condition', $group_rule)) {
                    $this->parseGroupToQuery($query, $group_rule, $rule['condition']);
                } else {
                    $this->parseRuleToQuery($query, $group_rule, $rule['condition']);
                }
            }
        }, null, null, $parent_condition ?? $rule['condition']);
    }

    /**
     * @param Builder $query
     * @param array $rule
     * @param string $condition AND or OR
     * @return Builder
     */
    protected function parseRuleToQuery($query, $rule, $condition)
    {
        [$field, $op, $value] = $this->expandRule($rule);

        switch ($op) {
            case 'equal':
            case 'not_equal':
            case 'less':
            case 'less_or_equal':
            case 'greater':
            case 'greater_or_equal':
            case 'regex':
            case 'not_regex':
                return $query->where($field, self::$operators[$op], $value, $condition);
            case 'contains':
            case 'not_contains':
                return $query->where($field, self::$operators[$op], "%$value%", $condition);
            case 'begins_with':
            case 'not_begins_with':
                return $query->where($field, self::$operators[$op], "$value%", $condition);
            case 'ends_with':
            case 'not_ends_with':
                return $query->where($field, self::$operators[$op], "%$value", $condition);
            case 'is_empty':
            case 'is_not_empty':
                return $query->where($field, self::$operators[$op], '');
            case 'is_null':
            case 'is_not_null':
                return $query->whereNull($field, $condition, $op == 'is_not_null');
            case 'between':
            case 'not_between':
                return $query->whereBetween($field, $value, $condition, $op == 'not_between');
            case 'in':
            case 'not_in':
                $values = preg_split('/[, ]/', $value);
                if ($values !== false) {
                    return $query->whereIn($field, $values, $condition, $op == 'not_in');
                }
                Log::error('Could not parse in values, use comma or space delimiters');
                break;
            default:
                Log::error('Unhandled QueryBuilderFluentParser operation: ' . $op);
        }

        return $query;
    }

    /**
     * Extract field, operator and value from the rule and expand macros and raw values
     *
     * @param array $rule
     * @return array [field, operator, value]
     */
    protected function expandRule($rule)
    {
        $field = $rule['field'];
        if (Str::startsWith($field, 'macros.')) {
            $field = DB::raw($this->expandMacro($field));
        }

        $op = $rule['operator'];

        $value = $rule['value'];
        if (! is_array($value) && Str::startsWith($value, '`') && Str::endsWith($value, '`')) {
            $value = DB::raw($this->expandMacro(trim($value, '`')));
        }

        return [$field, $op, $value];
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    protected function joinTables($query)
    {
        if (! isset($this->builder['joins'])) {
            $this->generateJoins();
        }

        foreach ($this->builder['joins'] as $join) {
            [$rightTable, $left, $right] = $join;
            $query->leftJoin($rightTable, $left, $right);
        }

        return $query;
    }

    /**
     * Generate the joins for this rule and store them in the rule.
     * This is an expensive operation.
     *
     * @return $this
     */
    public function generateJoins()
    {
        $joins = [];
        foreach ($this->generateGlue() as $glue) {
            [$left, $right] = explode(' = ', $glue, 2);
            if (Str::contains($right, '.')) { // last line is devices.device_id = ? for alerting... ignore it
                [$leftTable, $leftKey] = explode('.', $left);
                [$rightTable, $rightKey] = explode('.', $right);
                $target_table = ($rightTable != 'devices' ? $rightTable : $leftTable);  // don't try to join devices

                $joins[] = [$target_table, $left, $right];
            }
        }

        $this->builder['joins'] = $joins;

        return $this;
    }
}
