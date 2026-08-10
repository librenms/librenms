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
        return (new static(DB::connection($connection)))->dumpInstance($tables_to_dump);
    }

    public function dumpInstance(array $tables = []): array
    {
        $this->adapter->setSessionState();

        $builder = $this->db->getSchemaBuilder();
        $tableList = empty($tables) ?
            collect($builder->getTables())->where('schema', $this->adapter->getSchemaName())->toArray() :
            array_map(fn ($t) => ['name' => $t], $tables);

        usort($tableList, fn ($a, $b) => strnatcasecmp((string) $a['name'], (string) $b['name']));

        $extras = $this->adapter->fetchExtras($tableList);
        $output = [];

        foreach ($tableList as $table) {
            $name = $table['name'];
            try {
                $output[$name] = [
                    'Columns' => array_map(fn ($c) => $this->adapter->mapColumn($c, $extras[$name] ?? []), $builder->getColumns($name)),
                    'Indexes' => $this->mapIndexes($builder->getIndexes($name)),
                    'Constraints' => $this->mapConstraints($name, $builder->getForeignKeys($name)),
                ];
            } catch (\Exception) {
                continue;
            }
        }

        return $output;
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
        usort($indexes, function ($a, $b) {
            if ($a['primary']) {
                return -1;
            }
            if ($b['primary']) {
                return 1;
            }

            return strnatcasecmp((string) $a['name'], (string) $b['name']);
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

    protected function mapConstraints(string $table, array $fks): array
    {
        usort($fks, fn ($a, $b) => strnatcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

        $mapped = [];
        foreach ($fks as $fk) {
            $name = (string) ($fk['name'] ?: $table . '_' . implode('_', $fk['columns']) . '_foreign');
            $extra = [];
            foreach (['on_delete', 'on_update'] as $action) {
                if ($fk[$action] && ! in_array(strtoupper((string) $fk[$action]), ['RESTRICT', 'NO ACTION'])) {
                    $extra[] = strtoupper(str_replace('_', ' ', $action)) . ' ' . strtoupper((string) $fk[$action]);
                }
            }
            $mapped[$name] = [
                'name' => $name,
                'foreign_key' => $fk['columns'][0],
                'table' => $fk['foreign_table'],
                'key' => $fk['foreign_columns'][0],
                'extra' => implode(' ', $extra),
            ];
        }

        return $mapped;
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
        $currentCols = array_change_key_case(array_column($current, null, 'Field'), CASE_LOWER);
        foreach ($master as $i => $cdata) {
            $field = $cdata['Field'];
            $lower = strtolower($field);
            if (! isset($currentCols[$lower])) {
                $this->addChange("Database: missing column ($table/$field)", $this->addColumnSql($table, $cdata, $master[$i - 1]['Field'] ?? null), $changes);
            } elseif (! $this->adapter->columnsMatch($cdata, $currentCols[$lower])) {
                $this->addChange("Database: incorrect column ($table/$field)", $this->updateTableSql($table, $field, $cdata), $changes);
            }
            unset($currentCols[$lower]);
        }
        foreach ($currentCols as $c) {
            $this->addChange("Database: extra column ($table/{$c['Field']})", $this->dropColumnSql($table, $c['Field']), $changes);
        }
    }

    private function syncIndexes(string $table, array $master, array $current, array &$changes): void
    {
        $currentIdx = array_change_key_case($current, CASE_LOWER);
        foreach ($master as $name => $data) {
            $lower = strtolower($name);
            if (! isset($currentIdx[$lower])) {
                $this->addChange("Database: missing index ($table/$name)", $this->addIndexSql($table, $data), $changes);
            } else {
                $c = $currentIdx[$lower];
                $colsMatch = array_map(strtolower(...), $data['Columns']) === array_map(strtolower(...), $c['Columns']);
                if (! $colsMatch || $data['Unique'] != $c['Unique']) {
                    $this->addChange("Database: incorrect index ($table/$name)", $this->updateIndexSql($table, $name, $data), $changes);
                }
            }
            unset($currentIdx[$lower]);
        }
        foreach ($currentIdx as $name => $_) {
            $this->addChange("Database: extra index ($table/$name)", $this->dropIndexSql($table, $name), $changes);
        }
    }

    private function syncConstraints(string $table, array $master, array $current, array &$changes): void
    {
        $currentFk = array_change_key_case($current, CASE_LOWER);
        foreach ($master as $name => $data) {
            $lower = strtolower($name);
            if (! isset($currentFk[$lower])) {
                $this->addChange("Database: missing constraint ($table/$name)", $this->addConstraintSql($table, $data), $changes);
            } elseif ($data != $currentFk[$lower]) {
                $this->addChange("Database: incorrect constraint ($table/$name)", [$this->dropConstraintSql($table, $name), $this->addConstraintSql($table, $data)], $changes);
            }
            unset($currentFk[$lower]);
        }
        foreach ($currentFk as $name => $_) {
            $this->addChange("Database: extra constraint ($table/$name)", $this->dropConstraintSql($table, $name), $changes);
        }
    }

    public function addTableSql(string $table, array $data): string
    {
        $queries = $this->db->pretend(fn () => LaravelSchema::create($table, function (Blueprint $b) use ($data): void {
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

        return implode("\n", array_column($queries, 'query')) . ';';
    }

    public function addColumnSql(string $table, array $cdata, ?string $prev): array
    {
        $queries = $this->db->pretend(fn () => LaravelSchema::table($table, function (Blueprint $b) use ($cdata, $prev): void {
            $col = $this->applyColumnToBlueprint($b, $cdata);
            if ($col) {
                empty($prev) ? $col->first() : $col->after($prev);
            }
        }));

        return array_map(fn ($q) => $q['query'] . ';', $queries);
    }

    public function updateTableSql(string $table, string $column, array $cdata): array
    {
        $queries = $this->db->pretend(fn () => LaravelSchema::table($table, function (Blueprint $b) use ($cdata, $column): void {
            $col = $this->applyColumnToBlueprint($b, $cdata, $column);
            if ($col) {
                $col->change();
            }
        }));

        return array_map(fn ($q) => $q['query'] . ';', $queries);
    }

    public function dropColumnSql(string $table, string $column): string
    {
        $queries = $this->db->pretend(fn () => LaravelSchema::table($table, fn (Blueprint $b) => $b->dropColumn($column)));

        return implode("\n", array_column($queries, 'query')) . ';';
    }

    public function addIndexSql(string $table, array $idata): string
    {
        $queries = $this->db->pretend(fn () => LaravelSchema::table($table, fn (Blueprint $b) => $this->applyIndexToBlueprint($b, $idata)));

        return implode("\n", array_column($queries, 'query')) . ';';
    }

    public function updateIndexSql(string $table, string $name, array $idata): array
    {
        $queries = $this->db->pretend(fn () => LaravelSchema::table($table, function (Blueprint $b) use ($name, $idata): void {
            $b->dropIndex($name);
            $this->applyIndexToBlueprint($b, $idata);
        }));

        return array_map(fn ($q) => $q['query'] . ';', $queries);
    }

    public function dropIndexSql(string $table, string $name): string
    {
        $queries = $this->db->pretend(fn () => LaravelSchema::table($table, fn (Blueprint $b) => $b->dropIndex($name)));

        return implode("\n", array_column($queries, 'query')) . ';';
    }

    public function dropTableSql(string $table): string
    {
        $queries = $this->db->pretend(fn () => LaravelSchema::drop($table));

        return implode("\n", array_column($queries, 'query')) . ';';
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
            'int' => 'integer', 'tinyint' => 'tinyInteger', 'smallint' => 'smallInteger', 'mediumint' => 'mediumInteger', 'bigint' => 'bigInteger',
            'varchar' => 'string', 'blob' => 'binary', 'mediumblob' => 'binary', 'longblob' => 'binary', 'datetime' => 'dateTime', default => $clean,
        };

        if (! method_exists($b, $method)) {
            return null;
        }

        if ($method === 'enum') {
            $col = $b->enum($field, $params);
        } else {
            $col = $b->{$method}($field, ...$params);
        }
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

    public function addConstraintSql(string $table, array $c): string
    {
        try {
            $queries = $this->db->pretend(fn () => LaravelSchema::table($table, function (Blueprint $b) use ($c): void {
                $this->applyConstraintToBlueprint($b, $c);
            }));

            if (empty($queries)) {
                return '';
            }

            return implode("\n", array_column($queries, 'query')) . ';';
        } catch (\Exception) {
            return $this->adapter->addConstraintSql($table, $c);
        }
    }

    public function dropConstraintSql(string $table, string $name): string
    {
        try {
            $queries = $this->db->pretend(fn () => LaravelSchema::table($table, fn (Blueprint $b) => $b->dropForeign($name)));

            if (empty($queries)) {
                return '';
            }

            return implode("\n", array_column($queries, 'query')) . ';';
        } catch (\Exception) {
            return $this->adapter->dropConstraintSql($table, $name);
        }
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
