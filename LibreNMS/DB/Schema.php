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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as LaravelSchema;
use Illuminate\Support\Str;
use LibreNMS\DB\Schema\Adapters\AdapterFactory;
use LibreNMS\DB\Schema\Adapters\SchemaAdapter;
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

    public function compare(array $master): array
    {
        $changes = [];
        $dbTables = $this->getLiveTables();
        $current = $this->dumpInstance(array_intersect(array_keys($master), $dbTables));

        foreach ($master as $table => $data) {
            if (empty($current[$table])) {
                $this->addChange("Database: missing table ($table)", $this->addTableSql($table, $data), $changes);
                continue;
            }

            $this->syncColumns($table, $data['Columns'], $current[$table]['Columns'], $changes);
            $this->syncIndexes($table, $data['Indexes'] ?? [], $current[$table]['Indexes'] ?? [], $changes);
            $this->syncConstraints($table, $data['Constraints'] ?? [], $current[$table]['Constraints'] ?? [], $changes);
        }

        foreach (array_diff($dbTables, array_keys($master)) as $table) {
            $this->addChange("Database: extra table ($table)", $this->dropTableSql($table), $changes);
        }

        $allSql = [];
        foreach ($changes as $c) {
            is_array($c['sql']) ? $allSql = [...$allSql, ...$c['sql']] : $allSql[] = $c['sql'];
        }

        $preSql = $this->adapter->getPreSql($allSql);
        if (! empty($preSql)) {
            array_unshift($changes, [
                'description' => 'Platform specific session setup',
                'sql' => $preSql,
            ]);
        }

        return $changes;
    }

    private function addChange(string $desc, string|array $sql, array &$changes): void
    {
        $changes[] = ['description' => $desc, 'sql' => $sql];
    }

    private function syncColumns(string $table, array $master, array $current, array &$changes): void
    {
        $currentCols = collect($current)->keyBy(fn ($c) => strtolower($c['Field']));

        collect($master)->each(function ($cdata, $i) use ($table, $master, &$currentCols, &$changes) {
            $field = $cdata['Field'];
            $current = $currentCols->pull(strtolower($field));

            if (! $current) {
                $this->addChange("Database: missing column ($table/$field)", $this->addColumnSql($table, $cdata, $master[$i - 1]['Field'] ?? null), $changes);
            } elseif (! $this->adapter->columnsMatch($cdata, $current)) {
                $this->addChange("Database: incorrect column ($table/$field)", $this->updateColumnSql($table, $field, $cdata), $changes);
            }
        });

        $currentCols->each(fn ($c) => $this->addChange("Database: extra column ($table/{$c['Field']})", $this->dropColumnSql($table, $c['Field']), $changes));
    }

    private function syncIndexes(string $table, array $master, array $current, array &$changes): void
    {
        $currentIdx = collect($current)->keyBy(fn ($c) => strtolower($c['Name']));

        collect($master)->each(function ($data, $name) use ($table, &$currentIdx, &$changes) {
            $lower = strtolower($name);
            $match = $currentIdx->get($lower);

            if (! $match) {
                $match = $currentIdx->first(fn ($iData) => $this->adapter->indexesMatch($data, $iData));
                if ($match) {
                    $lower = strtolower($match['Name']);
                }
            }

            if (! $match) {
                $this->addChange("Database: missing index ($table/$name)", $this->addIndexSql($table, $data), $changes);
            } elseif (! $this->adapter->indexesMatch($data, $match)) {
                $this->addChange("Database: incorrect index ($table/$name)", $this->updateIndexSql($table, $name, $data), $changes);
            }
            $currentIdx->forget($lower);
        });

        $currentIdx->each(fn ($_, $name) => $this->addChange("Database: extra index ($table/$name)", $this->dropIndexSql($table, $name), $changes));
    }

    private function syncConstraints(string $table, array $master, array $current, array &$changes): void
    {
        $currentFk = collect($current)->keyBy(fn ($c) => strtolower($c['name']));

        collect($master)->each(function ($data, $name) use ($table, &$currentFk, &$changes) {
            $lower = strtolower($name);
            $match = $currentFk->get($lower);

            if (! $match) {
                $match = $currentFk->first(fn ($cData) => $this->adapter->constraintsMatch($data, $cData));
                if ($match) {
                    $lower = strtolower($match['name']);
                }
            }

            if (! $match) {
                $this->addChange("Database: missing constraint ($table/$name)", $this->addConstraintSql($table, $data), $changes);
            } elseif (! $this->adapter->constraintsMatch($data, $match)) {
                $this->addChange("Database: incorrect constraint ($table/$name)", [$this->dropConstraintSql($table, $name), $this->addConstraintSql($table, $data)], $changes);
            }
            $currentFk->forget($lower);
        });

        $currentFk->each(fn ($_, $name) => $this->addChange("Database: extra constraint ($table/$name)", $this->dropConstraintSql($table, $name), $changes));
    }

    public function addTableSql(string $table, array $data): array
    {
        return $this->pretend(fn () => LaravelSchema::create($table, function (Blueprint $b) use ($data): void {
            foreach ($data['Columns'] as $c) {
                $this->applyColumnToBlueprint($b, $c);
            }
            foreach ($data['Indexes'] ?? [] as $i) {
                $this->applyIndexToBlueprint($b, $i);
            }
            foreach ($data['Constraints'] ?? [] as $c) {
                $this->applyConstraintToBlueprint($b, $c);
            }
        }));
    }

    public function addColumnSql(string $table, array $cdata, ?string $prev): array
    {
        return $this->pretend(fn () => LaravelSchema::table($table, function (Blueprint $b) use ($cdata, $prev): void {
            $col = $this->applyColumnToBlueprint($b, $cdata);
            if ($col) {
                empty($prev) ? $col->first() : $col->after($prev);
            }
        }));
    }

    public function updateColumnSql(string $table, string $column, array $cdata): array
    {
        return $this->pretend(fn () => LaravelSchema::table($table, function (Blueprint $b) use ($cdata, $column): void {
            $col = $this->applyColumnToBlueprint($b, $cdata, $column);
            if ($col) {
                $col->change();
            }
        }));
    }

    public function dropColumnSql(string $table, string $column): array
    {
        return $this->pretend(fn () => LaravelSchema::table($table, fn (Blueprint $b) => $b->dropColumn($column)));
    }

    public function addIndexSql(string $table, array $idata): array
    {
        return $this->pretend(fn () => LaravelSchema::table($table, fn (Blueprint $b) => $this->applyIndexToBlueprint($b, $idata)));
    }

    public function updateIndexSql(string $table, string $name, array $idata): array
    {
        return $this->pretend(fn () => LaravelSchema::table($table, function (Blueprint $b) use ($name, $idata): void {
            $b->dropIndex($name);
            $this->applyIndexToBlueprint($b, $idata);
        }));
    }

    public function dropIndexSql(string $table, string $name): array
    {
        return $this->pretend(fn () => LaravelSchema::table($table, fn (Blueprint $b) => $b->dropIndex($name)));
    }

    public function dropTableSql(string $table): array
    {
        return $this->pretend(fn () => LaravelSchema::drop($table));
    }

    protected function applyColumnToBlueprint(Blueprint $b, array $cdata, ?string $old = null): ?\Illuminate\Database\Schema\ColumnDefinition
    {
        $type = (string) $cdata['Type'];
        $field = $old ?? $cdata['Field'];
        $unsigned = str_contains($type, 'unsigned');
        $clean = str_replace(' unsigned', '', $type);
        $params = [];

        if (preg_match('/^(\w+)\((.*)\)$/', $clean, $m)) {
            $clean = $m[1];
            $params = str_getcsv($m[2], ',', "'", '\\');
        }

        $method = match ($clean) {
            'int' => 'integer',
            'tinyint' => 'tinyInteger',
            'smallint' => 'smallInteger',
            'mediumint' => 'mediumInteger',
            'bigint' => 'bigInteger',
            'varchar' => 'string',
            'blob', 'mediumblob', 'longblob' => 'binary',
            'datetime' => 'dateTime',
            default => $clean,
        };

        if (! method_exists($b, $method)) {
            return null;
        }

        $col = ($method === 'enum') ? $b->enum($field, $params) : $b->{$method}($field, ...$params);

        if ($unsigned) {
            $col->unsigned();
        }

        $col->nullable((bool) $cdata['Null']);

        if (isset($cdata['Default'])) {
            $cdata['Default'] === 'CURRENT_TIMESTAMP' ? $col->useCurrent() : $col->default($cdata['Default']);
        }

        if ($cdata['Extra'] === 'auto_increment') {
            $col->autoIncrement();
        }

        if ($cdata['Extra'] === 'on update CURRENT_TIMESTAMP') {
            $col->useCurrentOnUpdate();
        }

        if ($old && $old !== $cdata['Field']) {
            $b->renameColumn($old, $cdata['Field']);
        }

        return $col;
    }

    protected function applyIndexToBlueprint(Blueprint $b, array $idata): void
    {
        $cols = $idata['Columns'];
        $name = $idata['Name'];
        if ($name === 'PRIMARY') {
            $b->primary($cols);
        } elseif ($idata['Unique']) {
            $b->unique($cols, $name);
        } else {
            $b->index($cols, $name);
        }
    }

    public function addConstraintSql(string $table, array $c): array
    {
        try {
            return $this->pretend(fn () => LaravelSchema::table($table, function (Blueprint $b) use ($c): void {
                $this->applyConstraintToBlueprint($b, $c);
            }));
        } catch (\Exception) {
            return (array) $this->adapter->addConstraintSql($table, $c);
        }
    }

    public function dropConstraintSql(string $table, string $name): array
    {
        try {
            return $this->pretend(fn () => LaravelSchema::table($table, fn (Blueprint $b) => $b->dropForeign($name)));
        } catch (\Exception) {
            return (array) $this->adapter->dropConstraintSql($table, $name);
        }
    }

    private function pretend(callable $callback): array
    {
        return array_map(fn ($q) => $q['query'] . ';', $this->db->pretend($callback));
    }

    protected function applyConstraintToBlueprint(Blueprint $b, array $c): void
    {
        $fk = $b->foreign($c['foreign_key'], $c['name'])->references($c['key'])->on($c['table']);
        if (str_contains(strtoupper((string) ($c['extra'] ?? '')), 'ON DELETE CASCADE')) {
            $fk->onDelete('cascade');
        } elseif (str_contains(strtoupper((string) ($c['extra'] ?? '')), 'ON DELETE SET NULL')) {
            $fk->onDelete('set null');
        }
        if (str_contains(strtoupper((string) ($c['extra'] ?? '')), 'ON UPDATE CASCADE')) {
            $fk->onUpdate('cascade');
        }
    }
}
