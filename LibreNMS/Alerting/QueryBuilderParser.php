<?php
/**
 * QueryBuilderParser.php
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

namespace LibreNMS\Alerting;

use LibreNMS\Config;
use Symfony\Component\Yaml\Yaml;

class QueryBuilderParser implements \JsonSerializable
{
    private static $table_blacklist = [
        'devices_perms',
        'bill_perms',
        'ports_perms',
    ];

    private static $key_aliases = [
        'app_id' => 'applications',
    ];

    private static $legacy_operators = [
        '=' => 'equal',
        '!=' => 'not_equal',
        '~' => 'regex',
        '!~' => 'not_regex',
        '<' => 'less',
        '>' => 'greater',
        '<=' => 'less_or_equal',
        '>=' => 'greater_or_equal',
    ];
    private static $operators = [
        'equal' => "=",
        'not_equal' => "!=",
        'in' => "IN (?)",
        'not_in' => "NOT IN (_REP_)",
        'less' => "<",
        'less_or_equal' => "<=",
        'greater' => ">",
        'greater_or_equal' => ">=",
        'begins_with' => "ILIKE",
        'not_begins_with' => "NOT ILIKE",
        'contains' => "ILIKE",
        'not_contains' => "NOT ILIKE",
        'ends_with' => "ILIKE",
        'not_ends_with' => "NOT ILIKE",
        'is_empty' => "=''",
        'is_not_empty' => "!=''",
        'is_null' => "IS NULL",
        'is_not_null' => "IS NOT NULL",
        'regex' => 'REGEXP',
        'not_regex' => 'NOT REGEXP',
    ];
    private static $like_operators = [
        'begins_with',
        'not_begins_with',
        'contains',
        'not_contains',
        'ends_with',
        'not_ends_with',
    ];

    private $builder = [];

    private function __construct(array $builder)
    {
        $this->builder = $builder;
    }

    // FIXME macros
    public function getTables()
    {
        if (!isset($this->tables)) {
            $tables = [];

            foreach ($this->builder['rules'] as $rule) {
                if (array_key_exists('rules', $rule)) {
                    $tables = array_merge($tables, $this->findTables($rule));
                } elseif (str_contains($rule['field'], '.')) {
                    list($table, $column) = explode('.', $rule['field']);
                    $tables[] = $table;
                }
            }

            $this->tables = array_keys(array_flip($tables));
        }

        return $this->tables;
    }

    public static function fromJson($json)
    {
        if (!is_array($json)) {
            $json = json_decode($json, true);
        }

        return new static($json);
    }

    public static function fromOld($query)
    {
        $condition = null;
        $rules = [];
        $filter = new QueryBuilderFilter();

        $split = array_chunk(preg_split('/(&&|\|\|)/', $query, -1, PREG_SPLIT_DELIM_CAPTURE), 2);

        foreach ($split as $chunk) {
            list($rule_text, $rule_operator) = $chunk;
            if (!isset($condition)) {
                // only allow one condition.  Since old rules had no grouping, this should hold logically
                $condition = ($rule_operator == '||' ? 'OR' : 'AND');
            }

            list($field, $op, $value) = preg_split('/ *([!=<>~]{1,2}) */', trim($rule_text), 2,
                PREG_SPLIT_DELIM_CAPTURE);
            $field = ltrim($field, '%');

            // for rules missing values just use '= 1'
            $operator = isset(self::$legacy_operators[$op]) ? self::$legacy_operators[$op] : 'equal';
            if (is_null($value)) {
                $value = '1';
            } else {
                $value = trim($value, '"');

                // value is a field, mark it with backticks
                if (starts_with($value, '%')) {
                    $value = '`' . ltrim($value, '%') . '`';
                }
            }

            $filter_item = $filter->getFilter($field);

            $type = $filter_item['type'];
            $input = isset($filter_item['input']) ? $filter_item['input'] : 'text';

            $rules[] = [
                'id' => $field,
                'field' => $field,
                'type' => $type,
                'input' => $input,
                'operator' => $operator,
                'value' => $value,
            ];
        }

        $builder = [
            'condition' => $condition,
            'rules' => $rules,
            'valid' => true,
        ];

        return new static($builder);
    }

    public function getRules()
    {

    }

    public function toSql($expand = false)
    {
        if (empty($this->builder) || !array_key_exists('condition', $this->builder)) {
            return null;
        }

        $result = [];
        foreach ($this->builder['rules'] as $rule) {
            if (array_key_exists('condition', $rule)) {
                $result[] = $this->parseGroup($rule);
            } else {
                $result[] = $this->parseRule($rule);
            }
        }

        return implode(" {$this->builder['condition']} ", $result);
    }

    private function parseGroup($rule)
    {
        $group_rules = [];

        foreach ($rule['rules'] as $group_rule) {
            if (array_key_exists('condition', $group_rule)) {
                $group_rules[] = $this->parseGroup($group_rule);
            } else {
                $group_rules[] = $this->parseRule($group_rule);
            }
        }

        $sql = implode(" {$rule['condition']} ", $group_rules);
        return "($sql)";
    }

    private function parseRule($rule)
    {
        $op = self::$operators[$rule['operator']];
        $value = $rule['value'];

        if (starts_with($value, '`') && ends_with($value, '`')) {
            // pass through value such as field
            $value = trim($value, '`');

        } elseif ($rule['type'] != 'integer') {
            $value = "\"$value\"";
        }

        $sql = "{$rule['field']} $op $value";

        return $sql;
    }

    private function mapRecursive(array $tables, $target, $history = [])
    {
        $relationships = $this->getTableRelationships();

        d_echo("Starting Tables: " . json_encode($tables) . PHP_EOL);
        if (!empty($history)) {
            $tables = array_diff($tables, $history);
            d_echo("Filtered Tables: " . json_encode($tables) . PHP_EOL);
        }

        foreach ($tables as $table) {
            $table_relations = $relationships[$table];
            $path = [$table];
            d_echo("Searching $table: " . json_encode($table_relations) . PHP_EOL);

            if (!empty($table_relations)) {
                if (in_array($target, $relationships[$table])) {
                    d_echo("Found in $table\n");
                    return $path; // found it
                } else {
                    $recurse = $this->mapRecursive($relationships[$table], $target, array_merge($history, $tables));
                    if ($recurse) {
                        $path = array_merge($path, $recurse);
                        return $path;
                    }
                }
            } else {
                $relations = array_keys(array_filter($relationships, function ($related) use ($table) {
                    return in_array($table, $related);
                }));

                d_echo("Dead end at $table, searching for relationships " . json_encode($relations) . PHP_EOL);
                $recurse = $this->mapRecursive($relations, $target, array_merge($history, $tables));
                if ($recurse) {
                    $path = array_merge($path, $recurse);
                    return $path;
                }
            }
        }

        return false;
    }

    public function buildMap($start, $target = 'devices')
    {
        d_echo("Searching for target: $target, starting with $start\n");

        if ($start == $target) {
            // um, yeah, we found it...
            return [$target];
        }

        $path = $this->mapRecursive([$start], $target);

        if ($path === false) {
            return $path;
        }

        $pairs = [];
        // scan through in pairs
        for ($i = 1; $i < count($path); $i++) {
            $pairs[] = [$path[$i -1], $path[$i]];
        }

        return $pairs;
    }

    private function getSchema()
    {
        if (!isset($this->schema)) {
            $this->schema = Yaml::parse(file_get_contents(Config::get('install_dir') . '/misc/db_schema.yaml'));
        }

        return $this->schema;
    }

    public function getTableFromKey($key)
    {
        if (ends_with($key, '_id')) {
            // hardcoded
            if ($key == 'app_id') {
                return 'applications';
            }

            // try to guess assuming key_id = keys table
            $guessed_table = substr($key, 0, -3);

            if (!ends_with($guessed_table, 's')) {
                if (ends_with($guessed_table, 'x')) {
                    $guessed_table .= 'es';
                } else {
                    $guessed_table .= 's';
                }
            }

            if (array_key_exists($guessed_table, $this->getSchema())) {
                return $guessed_table;
            }
        }

        return null;
    }

    /**
     * Get the primary key column(s) for a table
     *
     * @param string $table
     * @return string|array if a single column just the name is returned, otherwise an array of column names
     */
    public function getPrimaryKey($table) {
        $schema = $this->getSchema();

        $columns = $schema[$table]['Indexes']['PRIMARY']['Columns'];

        if (count($columns) == 1) {
            return reset($columns);
        }

        return $columns;
    }

    public function getTableRelationships()
    {
        if (!isset($this->relationships)) {
            $schema = $this->getSchema();

            $relations = array_column(array_map(function ($table, $data) {
                $columns = array_column($data['Columns'], 'Field');

                $related = array_filter(array_map(function ($column) use ($table) {
                    $guess = $this->getTableFromKey($column);
                    if ($guess != $table) {
                        return $guess;
                    }

                    return null;
                }, $columns));
//            echo "$table:";
//            var_dump($valid_parents);

                return [$table, $related];
            }, array_keys($schema), $schema), 1, 0);

            // filter out blacklisted tables
            $this->relationships = array_diff_key($relations, array_flip(self::$table_blacklist));
        }

        return $this->relationships;
    }

    public function columnExists($table, $column)
    {
        $schema = $this->getSchema();

        $fields = array_column($schema[$table]['Columns'], 'Field');

        return in_array($column, $fields);
    }

    public function generateGlue($target = 'device_id')
    {
        $tables = $this->getTables();  // get all tables in query

        if (array_key_exists('devices', $tables)) {
            return 'devices.device_id = ?';
        }

        $glues = [];
        foreach ($tables as $table) {
            $glue = $this->buildMap($table, $target);
            var_dump($glue);exit;
            $glues = array_merge($glues, $glue);
        }
        $glues = array_unique($glues);

        $where = [];

        foreach ($glues as $glue) {
            list($left, $right) = $glue;

            $rkey = $this->getPrimaryKey($right);
            $lkey = $rkey;

            if (!$this->columnExists($left, $lkey)) {
                $lkey = rtrim($right, 's') . '_id';
                if (!$this->columnExists($left, $lkey)) {
                    throw new \Exception('FIXME');
                }
            }

            $sql[] = "`$left`.`$lkey` = `$right`.`$rkey`";
        }

        return '(' . implode(' AND ', $where) . ')';
    }

    public function toArray()
    {
        return $this->builder;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->builder;
    }
}
