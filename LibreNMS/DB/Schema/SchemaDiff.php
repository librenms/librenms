<?php

/**
 * SchemaDiff.php
 *
 * Class for comparing and syncing schemas
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
 * @copyright  2024 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\DB\Schema;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use LibreNMS\DB\Schema\Adapters\SchemaAdapter;

class SchemaDiff
{
    private array $changes = [];

    public function __construct(
        protected Connection $db,
        protected SchemaAdapter $adapter
    ) {
    }

    public function compare(array $master, array $current, array $liveTables): array
    {
        $this->changes = [];

        foreach ($master as $table => $data) {
            if (empty($current[$table])) {
                $this->addChange("Database: missing table ($table)", $this->addTableSql($table, $data));
                continue;
            }

            $this->syncColumns($table, $data['Columns'], $current[$table]['Columns']);
            $this->syncIndexes($table, $data['Indexes'] ?? [], $current[$table]['Indexes'] ?? []);
            $this->syncConstraints($table, $data['Constraints'] ?? [], $current[$table]['Constraints'] ?? []);
        }

        foreach (array_diff($liveTables, array_keys($master)) as $table) {
            $this->addChange("Database: extra table ($table)", $this->dropTableSql($table));
        }

        $allSql = [];
        foreach ($this->changes as $c) {
            if (is_array($c['sql'])) {
                $allSql = [...$allSql, ...$c['sql']];
            } else {
                $allSql[] = $c['sql'];
            }
        }

        $preSql = $this->adapter->getPreSql($allSql);
        if (! empty($preSql)) {
            array_unshift($this->changes, [
                'description' => 'Platform specific session setup',
                'sql' => $preSql,
            ]);
        }

        return $this->changes;
    }

    protected function addChange(string $desc, string|array $sql): void
    {
        $this->changes[] = ['description' => $desc, 'sql' => $sql];
    }

    protected function syncColumns(string $table, array $master, array $current): void
    {
        $currentCols = collect($current)->keyBy(fn ($c) => strtolower($c['Field']));

        collect($master)->each(function ($cdata, $i) use ($table, $master, &$currentCols) {
            $field = $cdata['Field'];
            $current = $currentCols->pull(strtolower($field));

            if (! $current) {
                $this->addChange("Database: missing column ($table/$field)", $this->addColumnSql($table, $cdata, $master[$i - 1]['Field'] ?? null));
            } elseif (! $this->adapter->columnsMatch($cdata, $current)) {
                $this->addChange("Database: incorrect column ($table/$field)", $this->updateColumnSql($table, $field, $cdata));
            }
        });

        $currentCols->each(fn ($c) => $this->addChange("Database: extra column ($table/{$c['Field']})", $this->dropColumnSql($table, $c['Field'])));
    }

    protected function syncIndexes(string $table, array $master, array $current): void
    {
        $currentIdx = collect($current)->keyBy(fn ($c) => strtolower($c['Name']));

        collect($master)->each(function ($data, $name) use ($table, &$currentIdx) {
            $lower = strtolower($name);
            $match = $currentIdx->get($lower);

            if (! $match) {
                $match = $currentIdx->first(fn ($iData) => $this->adapter->indexesMatch($data, $iData));
                if ($match) {
                    $lower = strtolower($match['Name']);
                }
            }

            if (! $match) {
                $this->addChange("Database: missing index ($table/$name)", $this->addIndexSql($table, $data));
            } elseif (! $this->adapter->indexesMatch($data, $match)) {
                $this->addChange("Database: incorrect index ($table/$name)", $this->updateIndexSql($table, $name, $data));
            }
            $currentIdx->forget($lower);
        });

        $currentIdx->each(fn ($_, $name) => $this->addChange("Database: extra index ($table/$name)", $this->dropIndexSql($table, $name)));
    }

    protected function syncConstraints(string $table, array $master, array $current): void
    {
        $currentFk = collect($current)->keyBy(fn ($c) => strtolower($c['name']));

        collect($master)->each(function ($data, $name) use ($table, &$currentFk) {
            $lower = strtolower($name);
            $match = $currentFk->get($lower);

            if (! $match) {
                $match = $currentFk->first(fn ($cData) => $this->adapter->constraintsMatch($data, $cData));
                if ($match) {
                    $lower = strtolower($match['name']);
                }
            }

            if (! $match) {
                $this->addChange("Database: missing constraint ($table/$name)", $this->addConstraintSql($table, $data));
            } elseif (! $this->adapter->constraintsMatch($data, $match)) {
                $this->addChange("Database: incorrect constraint ($table/$name)", [...$this->dropConstraintSql($table, $name), ...$this->addConstraintSql($table, $data)]);
            }
            $currentFk->forget($lower);
        });

        $currentFk->each(fn ($_, $name) => $this->addChange("Database: extra constraint ($table/$name)", $this->dropConstraintSql($table, $name)));
    }

    protected function addTableSql(string $table, array $data): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->create($table, function (Blueprint $b) use ($data): void {
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

    protected function addColumnSql(string $table, array $cdata, ?string $prev): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, function (Blueprint $b) use ($cdata, $prev): void {
            $col = $this->applyColumnToBlueprint($b, $cdata);
            if ($col) {
                empty($prev) ? $col->first() : $col->after($prev);
            }
        }));
    }

    protected function updateColumnSql(string $table, string $column, array $cdata): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, function (Blueprint $b) use ($cdata, $column): void {
            $col = $this->applyColumnToBlueprint($b, $cdata, $column);
            if ($col) {
                $col->change();
            }
        }));
    }

    protected function dropColumnSql(string $table, string $column): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, fn (Blueprint $b) => $b->dropColumn($column)));
    }

    protected function addIndexSql(string $table, array $idata): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, fn (Blueprint $b) => $this->applyIndexToBlueprint($b, $idata)));
    }

    protected function updateIndexSql(string $table, string $name, array $idata): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, function (Blueprint $b) use ($name, $idata): void {
            $b->dropIndex($name);
            $this->applyIndexToBlueprint($b, $idata);
        }));
    }

    protected function dropIndexSql(string $table, string $name): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, fn (Blueprint $b) => $b->dropIndex($name)));
    }

    protected function dropTableSql(string $table): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->drop($table));
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

    protected function addConstraintSql(string $table, array $c): array
    {
        try {
            return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, function (Blueprint $b) use ($c): void {
                $this->applyConstraintToBlueprint($b, $c);
            }));
        } catch (\Exception) {
            return (array) $this->adapter->addConstraintSql($table, $c);
        }
    }

    protected function dropConstraintSql(string $table, string $name): array
    {
        try {
            return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, fn (Blueprint $b) => $b->dropForeign($name)));
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
