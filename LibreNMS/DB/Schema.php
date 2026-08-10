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

use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as LaravelSchema;
use Illuminate\Support\Str;
use LibreNMS\DB\Schema\Adapters\AdapterFactory;
use LibreNMS\DB\Schema\Adapters\SchemaAdapter;
use LibreNMS\DB\Schema\SchemaDiff;
use LibreNMS\Util\Version;
use Symfony\Component\Yaml\Yaml;

class Schema
{
    private static array $relationship_blacklist = ['devices_perms', 'bill_perms', 'ports_perms'];
    private array $relationships;
    private array $schema;
    protected Connection $db;
    protected SchemaAdapter $adapter;

    public function __construct(?Connection $db = null, ?SchemaAdapter $adapter = null)
    {
        $this->db = $db ?: DB::connection();
        $this->adapter = $adapter ?: AdapterFactory::create($this->db);
    }

    public static function isCurrent(): bool
    {
        return LaravelSchema::hasTable('migrations') && self::getMigrationFiles()->diff(self::getAppliedMigrations())->isEmpty();
    }

    public static function getUnexpectedMigrations(): Collection
    {
        return self::getAppliedMigrations()->diff(self::getMigrationFiles());
    }

    private static function getMigrationFiles(): Collection
    {
        return collect(glob(base_path('database/migrations/*.php')))->map(fn ($f) => basename($f, '.php'));
    }

    private static function getAppliedMigrations(): Collection
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
        if (isset($this->relationships)) {
            return $this->relationships;
        }

        $relationships = [];
        $schema = $this->getSchema();

        foreach ($schema as $table => $data) {
            if (in_array($table, self::$relationship_blacklist)) {
                continue;
            }

            $relations = [];
            foreach ($data['Columns'] as $column) {
                $guess = $this->getTableFromKey($column['Field']);
                if ($guess && $guess !== $table) {
                    $relations[] = $guess;
                }
            }
            $relationships[$table] = array_values(array_unique($relations));
        }

        return $this->relationships = $relationships;
    }

    public function getTableFromKey(string $key): ?string
    {
        if ($key === 'app_id') {
            return 'applications';
        }

        if (! Str::endsWith($key, '_id')) {
            return null;
        }

        $guessed = Str::plural(Str::beforeLast($key, '_id'));

        return array_key_exists($guessed, $this->getSchema()) ? $guessed : null;
    }

    public function columnExists(string $table, string $column): bool
    {
        return in_array($column, $this->getColumns($table));
    }

    /**
     * @param  string|Connection|null  $connection
     * @param  string[]  $tables_to_dump
     * @return array<string, array<string, mixed>>
     */
    public static function dump(string|Connection|null $connection = null, array $tables_to_dump = []): array
    {
        $db = $connection instanceof Connection ? $connection : DB::connection((string) $connection);

        return (new static($db))->dumpInstance($tables_to_dump);
    }

    /**
     * @param  string[]  $tables
     * @return array<string, array<string, mixed>>
     */
    public function dumpInstance(array $tables = []): array
    {
        $this->adapter->setSessionState();

        $builder = $this->db->getSchemaBuilder();
        $schemaName = $this->adapter->getSchemaName();
        $tableList = empty($tables) ?
            array_filter($builder->getTables(), fn ($t) => $t['schema'] === $schemaName) :
            array_map(fn ($t) => ['name' => $t], $tables);

        usort($tableList, fn ($a, $b) => strnatcasecmp((string) $a['name'], (string) $b['name']));

        /** @var array<int, array{name: string}> $tableList */
        $extras = $this->adapter->fetchExtras($tableList);

        $dump = [];
        foreach ($tableList as $table) {
            $name = $table['name'];
            try {
                $dump[$name] = [
                    'Columns' => array_map(fn ($c) => $this->adapter->mapColumn($c, $extras[$name] ?? []), $builder->getColumns($name)),
                    'Indexes' => $this->mapIndexes($builder->getIndexes($name)),
                    'Constraints' => $this->mapConstraints($name, $builder->getForeignKeys($name)),
                ];
            } catch (\Exception) {
                // skip failed tables
            }
        }

        return $dump;
    }

    public function getLiveTables(): array
    {
        $schemaName = $this->adapter->getSchemaName();
        $tables = array_filter($this->db->getSchemaBuilder()->getTables(), fn ($t) => $t['schema'] === $schemaName);

        return array_column($tables, 'name');
    }

    /**
     * @param  array<int, array<string, mixed>>  $indexes
     * @return array<string, array{Name: string, Columns: string[], Unique: bool, Type: string}>
     */
    protected function mapIndexes(array $indexes): array
    {
        usort($indexes, fn ($a, $b) => $a['primary'] ? -1 : ($b['primary'] ? 1 : strnatcasecmp((string) $a['name'], (string) $b['name'])));

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

    /**
     * @param  array<int, array<string, mixed>>  $fks
     * @return array<string, array{name: string, foreign_key: string, table: string, key: string, extra: string}>
     */
    protected function mapConstraints(string $table, array $fks): array
    {
        usort($fks, fn ($a, $b) => strnatcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

        $mapped = [];
        foreach ($fks as $fk) {
            $name = (string) ($fk['name'] ?: $table . '_' . implode('_', $fk['columns']) . '_foreign');

            $extraParts = [];
            foreach (['on_delete', 'on_update'] as $action) {
                $val = strtoupper((string) ($fk[$action] ?? ''));
                if ($val && ! in_array($val, ['RESTRICT', 'NO ACTION'])) {
                    $extraParts[] = strtoupper(str_replace('_', ' ', $action)) . ' ' . $val;
                }
            }
            $extra = implode(' ', $extraParts);

            $mapped[$name] = [
                'name' => $name,
                'foreign_key' => $fk['columns'][0],
                'table' => $fk['foreign_table'],
                'key' => $fk['foreign_columns'][0],
                'extra' => $extra,
            ];
        }

        return $mapped;
    }

    /**
     * @param  array<string, mixed>|string|null  $master
     * @return array<int, array{description: string, sql: string|string[]}>
     */
    public function compare(array|string|null $master = null): array
    {
        if (is_string($master) || $master === null) {
            $schema_file = $master ?? resource_path('definitions/schema/db_schema.yaml');
            $master = (array) Yaml::parse(file_get_contents($schema_file));
        }

        $dbTables = $this->getLiveTables();
        $current = $this->dumpInstance(array_intersect(array_keys($master), $dbTables));

        return (new SchemaDiff($this->db, $this->adapter))
            ->compare($master, $current, $dbTables);
    }
}
