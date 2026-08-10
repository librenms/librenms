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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\DB;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LibreNMS\Util\Version;
use Schema as LaravelSchema;
use Symfony\Component\Yaml\Yaml;

class Schema
{
    private static array $relationship_blacklist = ['devices_perms', 'bill_perms', 'ports_perms'];
    private array $relationships;
    private array $schema;

    public static function isCurrent(): bool
    {
        return LaravelSchema::hasTable('migrations') && self::getMigrationFiles()->diff(self::getAppliedMigrations())->isEmpty();
    }

    public static function getUnexpectedMigrations(): \Illuminate\Support\Collection
    {
        return self::getAppliedMigrations()->diff(self::getMigrationFiles());
    }

    private static function getMigrationFiles(): \Illuminate\Support\Collection
    {
        return collect(glob(base_path('database/migrations/*.php')))->map(fn ($f) => basename($f, '.php'));
    }

    private static function getAppliedMigrations(): \Illuminate\Support\Collection
    {
        return Eloquent::DB()->table('migrations')->pluck('migration');
    }

    public function getPrimaryKey(string $table): string
    {
        return array_first($this->getSchema()[$table]['Indexes']['PRIMARY']['Columns']);
    }

    public function getSchema(): array
    {
        return $this->schema ??= Yaml::parse(file_get_contents(resource_path('definitions/schema/db_schema.yaml')));
    }

    public function getTables(): array
    {
        return array_keys($this->getSchema());
    }

    public function getColumns(string $table): array
    {
        return array_column($this->getSchema()[$table]['Columns'], 'Field');
    }

    public function getAllRelationshipPaths(string $base = 'devices'): array
    {
        $db_version = Version::get()->databaseMigrationCount();

        return Cache::remember("schema_relationships_{$base}_{$db_version}", 86400, function () use ($base) {
            $paths = [];
            foreach ($this->getTables() as $table) {
                if ($path = $this->findPathRecursive([$table], $base)) {
                    $paths[$table] = $path;
                }
            }

            return $paths;
        });
    }

    public function findRelationshipPath(string $target, string $start = 'devices'): array|bool
    {
        return ($target === $start) ? [$start] : ($this->getAllRelationshipPaths($start)[$target] ?? false);
    }

    private function findPathRecursive(array $tables, string $target, array $history = []): array|bool
    {
        $relationships = $this->getTableRelationships();
        $tables = array_diff($tables, $history);

        foreach ($tables as $table) {
            if (in_array($table, $relationships[$target] ?? [])) {
                return [$table, $target];
            }

            if (! empty($table_relations = $relationships[$table] ?? [])) {
                if ($recurse = $this->findPathRecursive($table_relations, $target, array_merge($history, $tables))) {
                    return array_merge($recurse, [$table]);
                }
            } else {
                $relations = array_keys(array_filter($relationships, fn ($related) => in_array($table, $related)));
                if ($recurse = $this->findPathRecursive($relations, $target, array_merge($history, $tables))) {
                    return array_merge($recurse, [$table]);
                }
            }
        }

        return false;
    }

    public function getTableRelationships(): array
    {
        if (! isset($this->relationships)) {
            $schema = $this->getSchema();
            $relations = [];
            foreach ($schema as $table => $data) {
                $related = array_filter(array_map(function ($column) use ($table) {
                    $guess = $this->getTableFromKey($column);

                    return ($guess && $guess !== $table) ? $guess : null;
                }, array_column($data['Columns'], 'Field')));
                $relations[$table] = array_values($related);
            }
            $this->relationships = array_diff_key($relations, array_flip(self::$relationship_blacklist));
        }

        return $this->relationships;
    }

    public function getTableFromKey(string $key): ?string
    {
        if ($key === 'app_id') return 'applications';
        if (! Str::endsWith($key, '_id')) return null;

        $guessed = substr($key, 0, -3);
        $guessed .= Str::endsWith($guessed, 'x') ? 'es' : (Str::endsWith($guessed, 's') ? '' : 's');

        return array_key_exists($guessed, $this->getSchema()) ? $guessed : null;
    }

    public function columnExists(string $table, string $column): bool
    {
        return in_array($column, $this->getColumns($table));
    }

    public static function dump($connection = null, array $tables_to_dump = []): array
    {
        $db = DB::connection($connection);
        if ($db->getDriverName() === 'mysql') $db->statement("SET TIME_ZONE='+00:00'");

        $builder = $db->getSchemaBuilder();
        $tables = empty($tables_to_dump) ?
            collect($builder->getTables())->where('schema', $db->getDatabaseName())->toArray() :
            array_map(fn ($t) => ['name' => $t], $tables_to_dump);

        usort($tables, fn ($a, $b) => strnatcasecmp($a['name'], $b['name']));

        $extras = self::fetchMysqlExtras($db, $tables);
        $output = [];

        foreach ($tables as $table) {
            $name = $table['name'];
            try {
                $output[$name] = [
                    'Columns' => array_map(fn ($c) => self::mapColumn($c, $extras[$name] ?? []), $builder->getColumns($name)),
                    'Indexes' => self::mapIndexes($builder->getIndexes($name)),
                    'Constraints' => self::mapConstraints($builder->getForeignKeys($name)),
                ];
            } catch (\Exception) {
                continue;
            }
        }

        return $output;
    }

    private static function fetchMysqlExtras($db, array $tables): array
    {
        if ($db->getDriverName() !== 'mysql' || empty($tables)) return [];

        $names = array_column($tables, 'name');
        $placeholders = implode(',', array_fill(0, count($names), '?'));
        $rows = $db->select("SELECT TABLE_NAME, COLUMN_NAME, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME IN ($placeholders)", array_merge([$db->getDatabaseName()], $names));

        $extras = [];
        foreach ($rows as $r) $extras[$r->TABLE_NAME][$r->COLUMN_NAME] = $r->EXTRA;

        return $extras;
    }

    private static function mapColumn(array $col, array $tableExtras): array
    {
        $extra = str_replace('current_timestamp()', 'CURRENT_TIMESTAMP', (string) ($tableExtras[$col['name']] ?? ''));
        $type = preg_replace('/int\([0-9]+\)/', 'int', (string) $col['type']);

        $def = [
            'Field' => $col['name'],
            'Type' => $type,
            'Null' => (bool) $col['nullable'],
            'Extra' => preg_replace('/DEFAULT_GENERATED[ ]*/', '', $extra),
        ];

        if (isset($col['default']) && strtoupper((string) $col['default']) !== 'NULL') {
            $default = (string) $col['default'];
            if (str_starts_with($default, "'") && str_ends_with($default, "'")) $default = substr($default, 1, -1);
            $def['Default'] = str_contains(strtolower($default), 'current_timestamp') ? 'CURRENT_TIMESTAMP' : $default;
        }

        return $def;
    }

    private static function mapIndexes(array $indexes): array
    {
        usort($indexes, function ($a, $b) {
            if ($a['primary']) {
                return -1;
            }
            if ($b['primary']) {
                return 1;
            }

            return strnatcasecmp($a['name'], $b['name']);
        });

        $mapped = [];
        foreach ($indexes as $i) {
            $name = $i['primary'] ? 'PRIMARY' : $i['name'];
            $mapped[$name] = [
                'Name' => $name,
                'Columns' => $i['columns'],
                'Unique' => (bool) $i['unique'],
                'Type' => strtoupper((string) ($i['type'] ?? 'BTREE')),
            ];
        }

        return $mapped;
    }

    private static function mapConstraints(array $fks): array
    {
        usort($fks, fn ($a, $b) => strnatcasecmp($a['name'], $b['name']));

        $mapped = [];
        foreach ($fks as $fk) {
            $extra = [];
            foreach (['on_delete', 'on_update'] as $action) {
                if ($fk[$action] && strtoupper((string) $fk[$action]) !== 'RESTRICT') {
                    $extra[] = strtoupper(str_replace('_', ' ', $action)) . ' ' . strtoupper((string) $fk[$action]);
                }
            }
            $mapped[$fk['name']] = [
                'name' => $fk['name'],
                'foreign_key' => $fk['columns'][0],
                'table' => $fk['foreign_table'],
                'key' => $fk['foreign_columns'][0],
                'extra' => implode(' ', $extra),
            ];
        }

        return $mapped;
    }
}
