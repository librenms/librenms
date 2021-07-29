<?php
/**
 * Schema.php
 *
 * Class for querying the schema
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\DB;

use DB;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Util\Version;
use PDOException;
use Schema as LaravelSchema;
use Symfony\Component\Yaml\Yaml;

class Schema
{
    private static $relationship_blacklist = [
        'devices_perms',
        'bill_perms',
        'ports_perms',
    ];

    private $relationships;
    private $schema;

    /**
     * Check the database to see if the migrations have all been run
     *
     * @return bool
     */
    public static function isCurrent()
    {
        if (LaravelSchema::hasTable('migrations')) {
            return self::getMigrationFiles()->diff(self::getAppliedMigrations())->isEmpty();
        }

        return false;
    }

    /**
     * Check for extra migrations and return them
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getUnexpectedMigrations()
    {
        return self::getAppliedMigrations()->diff(self::getMigrationFiles());
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private static function getMigrationFiles()
    {
        $migrations = collect(glob(base_path('database/migrations/') . '*.php'))
            ->map(function ($migration_file) {
                return basename($migration_file, '.php');
            });

        return $migrations;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private static function getAppliedMigrations()
    {
        return Eloquent::DB()->table('migrations')->pluck('migration');
    }

    /**
     * Get the primary key column(s) for a table
     *
     * @param string $table
     * @return string if a single column just the name is returned, otherwise the first column listed will be returned
     */
    public function getPrimaryKey($table)
    {
        $schema = $this->getSchema();
        $columns = $schema[$table]['Indexes']['PRIMARY']['Columns'];

        return reset($columns);
    }

    public function getSchema()
    {
        if (! isset($this->schema)) {
            $file = Config::get('install_dir') . '/misc/db_schema.yaml';
            $this->schema = Yaml::parse(file_get_contents($file));
        }

        return $this->schema;
    }

    /**
     * Get the schema version from the previous schema system
     */
    public static function getLegacySchema(): int
    {
        try {
            $db = \LibreNMS\DB\Eloquent::DB();
            if ($db) {
                return (int) $db->table('dbSchema')
                    ->orderBy('version', 'DESC')
                    ->value('version');
            }
        } catch (PDOException $e) {
            // return default
        }

        return 0;
    }

    /**
     * Get a list of all tables.
     *
     * @return array
     */
    public function getTables()
    {
        return array_keys($this->getSchema());
    }

    /**
     * Return all columns for the given table
     *
     * @param string $table
     * @return array
     */
    public function getColumns($table)
    {
        $schema = $this->getSchema();

        return array_column($schema[$table]['Columns'], 'Field');
    }

    /**
     * Get all relationship paths.
     * Caches the data after the first call as long as the schema hasn't changed
     *
     * @param string $base
     * @return mixed
     */
    public function getAllRelationshipPaths($base = 'devices')
    {
        $update_cache = true;
        $cache = [];
        $cache_file = Config::get('install_dir') . "/cache/{$base}_relationships.cache";
        $db_version = Version::get()->database();

        if (is_file($cache_file)) {
            $cache = unserialize(file_get_contents($cache_file));
            if ($cache['version'] == $db_version) {
                $update_cache = false;  // cache is valid skip update
            }
        }

        if ($update_cache) {
            $paths = [];
            foreach ($this->getTables() as $table) {
                $path = $this->findPathRecursive([$table], $base);
                if ($path) {
                    $paths[$table] = $path;
                }
            }

            $cache = [
                'version' => $db_version,
                $base => $paths,
            ];

            if (is_writable($cache_file)) {
                file_put_contents($cache_file, serialize($cache));
            } else {
                d_echo("Could not write cache file ($cache_file)!\n");
            }
        }

        return $cache[$base];
    }

    /**
     * Find the relationship path from $start to $target
     *
     * @param string $target
     * @param string $start Default: devices
     * @return array|false list of tables in path order, or false if no path is found
     */
    public function findRelationshipPath($target, $start = 'devices')
    {
        d_echo("Searching for target: $start, starting with $target\n");

        if ($target == $start) {
            // um, yeah, we found it...
            return [$start];
        }

        $all = $this->getAllRelationshipPaths($start);

        return isset($all[$target]) ? $all[$target] : false;
    }

    private function findPathRecursive(array $tables, $target, $history = [])
    {
        $relationships = $this->getTableRelationships();

        d_echo('Starting Tables: ' . json_encode($tables) . PHP_EOL);
        if (! empty($history)) {
            $tables = array_diff($tables, $history);
            d_echo('Filtered Tables: ' . json_encode($tables) . PHP_EOL);
        }

        foreach ($tables as $table) {
            // check for direct relationships
            if (in_array($table, $relationships[$target])) {
                d_echo("Direct relationship found $target -> $table\n");

                return [$table, $target];
            }

            $table_relations = $relationships[$table] ?? [];
            d_echo("Searching $table: " . json_encode($table_relations) . PHP_EOL);

            if (! empty($table_relations)) {
                if (in_array($target, $relationships[$table])) {
                    d_echo("Found in $table\n");

                    return [$target, $table]; // found it
                } else {
                    $recurse = $this->findPathRecursive($relationships[$table], $target, array_merge($history, $tables));
                    if ($recurse) {
                        return array_merge($recurse, [$table]);
                    }
                }
            } else {
                $relations = array_keys(array_filter($relationships, function ($related) use ($table) {
                    return in_array($table, $related);
                }));

                d_echo("Dead end at $table, searching for relationships " . json_encode($relations) . PHP_EOL);
                $recurse = $this->findPathRecursive($relations, $target, array_merge($history, $tables));
                if ($recurse) {
                    return array_merge($recurse, [$table]);
                }
            }
        }

        return false;
    }

    public function getTableRelationships()
    {
        if (! isset($this->relationships)) {
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

                // renumber $related array
                $related = array_values($related);

                return [$table, $related];
            }, array_keys($schema), $schema), 1, 0);

            // filter out blacklisted tables
            $this->relationships = array_diff_key($relations, array_flip(self::$relationship_blacklist));
        }

        return $this->relationships;
    }

    public function getTableFromKey($key)
    {
        if (Str::endsWith($key, '_id')) {
            // hardcoded
            if ($key == 'app_id') {
                return 'applications';
            }

            // try to guess assuming key_id = keys table
            $guessed_table = substr($key, 0, -3);

            if (! Str::endsWith($guessed_table, 's')) {
                if (Str::endsWith($guessed_table, 'x')) {
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

    public function columnExists($table, $column)
    {
        return in_array($column, $this->getColumns($table));
    }

    /**
     * Dump the database schema to an array.
     * The top level will be a list of tables
     * Each table contains the keys Columns and Indexes.
     *
     * Each entry in the Columns array contains these keys: Field, Type, Null, Default, Extra
     * Each entry in the Indexes array contains these keys: Name, Columns(array), Unique
     *
     * @param string $connection use a specific connection
     * @return array
     */
    public static function dump($connection = null)
    {
        $output = [];
        $db_name = DB::connection($connection)->getDatabaseName();

        foreach (DB::connection($connection)->select(DB::raw("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db_name' ORDER BY TABLE_NAME;")) as $table) {
            $table = $table->TABLE_NAME;
            foreach (DB::connection($connection)->select(DB::raw("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$db_name' AND TABLE_NAME='$table' ORDER BY ORDINAL_POSITION")) as $data) {
                $def = [
                    'Field' => $data->COLUMN_NAME,
                    'Type' => preg_replace('/int\([0-9]+\)/', 'int', $data->COLUMN_TYPE),
                    'Null' => $data->IS_NULLABLE === 'YES',
                    'Extra' => str_replace('current_timestamp()', 'CURRENT_TIMESTAMP', $data->EXTRA),
                ];

                if (isset($data->COLUMN_DEFAULT) && $data->COLUMN_DEFAULT != 'NULL') {
                    $default = trim($data->COLUMN_DEFAULT, "'");
                    $def['Default'] = str_replace('current_timestamp()', 'CURRENT_TIMESTAMP', $default);
                }
                // MySQL 8 fix, remove DEFAULT_GENERATED from timestamp extra columns
                if ($def['Type'] == 'timestamp') {
                    $def['Extra'] = preg_replace('/DEFAULT_GENERATED[ ]*/', '', $def['Extra']);
                }

                $output[$table]['Columns'][] = $def;
            }

            $keys = DB::connection($connection)->select(DB::raw("SHOW INDEX FROM `$table`"));
            usort($keys, function ($a, $b) {
                return $a->Key_name <=> $b->Key_name;
            });
            foreach ($keys as $key) {
                $key_name = $key->Key_name;
                if (isset($output[$table]['Indexes'][$key_name])) {
                    $output[$table]['Indexes'][$key_name]['Columns'][] = $key->Column_name;
                } else {
                    $output[$table]['Indexes'][$key_name] = [
                        'Name' => $key->Key_name,
                        'Columns' => [$key->Column_name],
                        'Unique' => ! $key->Non_unique,
                        'Type' => $key->Index_type,
                    ];
                }
            }

            $create = DB::connection($connection)->select(DB::raw("SHOW CREATE TABLE `$table`"))[0];

            if (isset($create->{'Create Table'})) {
                $constraint_regex = '/CONSTRAINT `(?<name>[A-Za-z_0-9]+)` FOREIGN KEY \(`(?<foreign_key>[A-Za-z_0-9]+)`\) REFERENCES `(?<table>[A-Za-z_0-9]+)` \(`(?<key>[A-Za-z_0-9]+)`\) ?(?<extra>[ A-Z]+)?/';
                $constraint_count = preg_match_all($constraint_regex, $create->{'Create Table'}, $constraints);
                for ($i = 0; $i < $constraint_count; $i++) {
                    $constraint_name = $constraints['name'][$i];
                    $output[$table]['Constraints'][$constraint_name] = [
                        'name' => $constraint_name,
                        'foreign_key' => $constraints['foreign_key'][$i],
                        'table' => $constraints['table'][$i],
                        'key' => $constraints['key'][$i],
                        'extra' => $constraints['extra'][$i],
                    ];
                }
            }
        }

        return $output;
    }
}
