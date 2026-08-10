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
        return $this->relationships ??= collect($this->getSchema())
            ->map(fn ($data, $table) => collect($data['Columns'])
                ->pluck('Field')
                ->map(fn ($column) => $this->getTableFromKey($column))
                ->filter(fn ($guess) => $guess && $guess !== $table)
                ->values()
                ->all()
            )
            ->except(self::$relationship_blacklist)
            ->all();
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

    public static function dump($connection = null, array $tables_to_dump = []): array
    {
        return (new static(DB::connection($connection)))->dumpInstance($tables_to_dump);
    }

    public function dumpInstance(array $tables = []): array
    {
        $this->adapter->setSessionState();

        $builder = $this->db->getSchemaBuilder();
        $tableList = empty($tables) ?
            collect($builder->getTables())->where('schema', $this->adapter->getSchemaName())->all() :
            array_map(fn ($t) => ['name' => $t], $tables);

        usort($tableList, fn ($a, $b) => strnatcasecmp((string) $a['name'], (string) $b['name']));

        $extras = $this->adapter->fetchExtras($tableList);

        return collect($tableList)
            ->mapWithKeys(function ($table) use ($builder, $extras) {
                $name = $table['name'];
                try {
                    return [$name => [
                        'Columns' => array_map(fn ($c) => $this->adapter->mapColumn($c, $extras[$name] ?? []), $builder->getColumns($name)),
                        'Indexes' => $this->mapIndexes($builder->getIndexes($name)),
                        'Constraints' => $this->mapConstraints($name, $builder->getForeignKeys($name)),
                    ]];
                } catch (\Exception) {
                    return [];
                }
            })
            ->all();
    }


    public function getLiveTables(): array
    {
        return collect($this->db->getSchemaBuilder()->getTables())
            ->where('schema', $this->adapter->getSchemaName())
            ->pluck('name')
            ->toArray();
    }

    protected function mapIndexes(array $indexes): array
    {
        return collect($indexes)
            ->sort(fn ($a, $b) => $a['primary'] ? -1 : ($b['primary'] ? 1 : strnatcasecmp((string) $a['name'], (string) $b['name'])))
            ->mapWithKeys(fn ($i) => [
                ($name = $i['primary'] ? 'PRIMARY' : $i['name']) => [
                    'Name' => $name,
                    'Columns' => $i['columns'],
                    'Unique' => (bool) $i['unique'],
                    'Type' => strtoupper((string) ($i['type'] ?? 'BTREE')),
                ],
            ])
            ->all();
    }

    protected function mapConstraints(string $table, array $fks): array
    {
        return collect($fks)
            ->sort(fn ($a, $b) => strnatcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')))
            ->mapWithKeys(function ($fk) use ($table) {
                $name = (string) ($fk['name'] ?: $table . '_' . implode('_', $fk['columns']) . '_foreign');
                $extra = collect(['on_delete', 'on_update'])
                    ->map(function ($action) use ($fk) {
                        $val = strtoupper((string) ($fk[$action] ?? ''));
                        return ($val && ! in_array($val, ['RESTRICT', 'NO ACTION']))
                            ? strtoupper(str_replace('_', ' ', $action)) . ' ' . $val
                            : null;
                    })
                    ->filter()
                    ->implode(' ');

                return [$name => [
                    'name' => $name,
                    'foreign_key' => $fk['columns'][0],
                    'table' => $fk['foreign_table'],
                    'key' => $fk['foreign_columns'][0],
                    'extra' => $extra,
                ]];
            })
            ->all();
    }

    public function compare(string|null $schema_file = null): array
    {
        $schema_file ??= resource_path('definitions/schema/db_schema.yaml');
        $master = (array) Yaml::parse(file_get_contents($schema_file));

        $dbTables = $this->getLiveTables();
        $current = $this->dumpInstance(array_intersect(array_keys($master), $dbTables));

        return (new SchemaDiff($this->db, $this->adapter))
            ->compare($master, $current, $dbTables);
    }
}
