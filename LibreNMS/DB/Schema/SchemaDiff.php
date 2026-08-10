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
    /** @var array<int, array{description: string, sql: string|string[]}> */
    private array $changes = [];

    public function __construct(
        protected Connection $db,
        protected SchemaAdapter $adapter
    ) {
    }

    /**
     * @param  array<string, array<string, mixed>>  $master
     * @param  array<string, array<string, mixed>>  $current
     * @param  string[]  $liveTables
     * @return array<int, array{description: string, sql: string|string[]}>
     */
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

        $allSql = collect($this->changes)->pluck('sql')->flatten()->map(fn ($s) => (string) $s)->all();

        $preSql = $this->adapter->getPreSql($allSql);
        if (! empty($preSql)) {
            array_unshift($this->changes, [
                'description' => 'Platform specific session setup',
                'sql' => $preSql,
            ]);
        }

        return $this->changes;
    }

    /**
     * @param  string|string[]  $sql
     */
    protected function addChange(string $desc, string|array $sql): void
    {
        $this->changes[] = ['description' => $desc, 'sql' => $sql];
    }

    /**
     * @param  array<int, array<string, mixed>>  $master
     * @param  array<int, array<string, mixed>>  $current
     */
    protected function syncColumns(string $table, array $master, array $current): void
    {
        $currentCols = [];
        foreach ($current as $c) {
            $currentCols[strtolower((string) $c['Field'])] = $c;
        }

        foreach ($master as $i => $cdata) {
            $field = (string) $cdata['Field'];
            $lowerField = strtolower($field);

            if (isset($currentCols[$lowerField])) {
                $match = $currentCols[$lowerField];
                unset($currentCols[$lowerField]);

                if (! $this->adapter->columnsMatch($cdata, $match)) {
                    $this->addChange("Database: incorrect column ($table/$field)", $this->updateColumnSql($table, $field, $cdata));
                }
            } else {
                $prevField = $master[$i - 1]['Field'] ?? null;
                $this->addChange("Database: missing column ($table/$field)", $this->addColumnSql($table, $cdata, $prevField));
            }
        }

        foreach ($currentCols as $c) {
            $this->addChange("Database: extra column ($table/{$c['Field']})", $this->dropColumnSql($table, (string) $c['Field']));
        }
    }

    /**
     * @param  array<string, array<string, mixed>>  $master
     * @param  array<string, array<string, mixed>>  $current
     */
    protected function syncIndexes(string $table, array $master, array $current): void
    {
        $currentIdx = [];
        foreach ($current as $c) {
            $currentIdx[strtolower((string) $c['Name'])] = $c;
        }

        foreach ($master as $name => $data) {
            $lower = strtolower((string) $name);
            $match = $currentIdx[$lower] ?? null;

            if (! $match) {
                $match = array_find($currentIdx, fn ($iData) => $this->adapter->indexesMatch($data, (array) $iData));
                if ($match) {
                    $lower = strtolower((string) $match['Name']);
                }
            }

            if (! $match) {
                $this->addChange("Database: missing index ($table/$name)", $this->addIndexSql($table, $data));
            } elseif (! $this->adapter->indexesMatch($data, (array) $match)) {
                $this->addChange("Database: incorrect index ($table/$name)", $this->updateIndexSql($table, (string) $name, $data));
            }

            unset($currentIdx[$lower]);
        }

        foreach ($currentIdx as $name => $data) {
            $this->addChange("Database: extra index ($table/$name)", $this->dropIndexSql($table, (string) $name));
        }
    }

    /**
     * @param  array<string, array<string, mixed>>  $master
     * @param  array<string, array<string, mixed>>  $current
     */
    protected function syncConstraints(string $table, array $master, array $current): void
    {
        $currentFk = [];
        foreach ($current as $c) {
            $currentFk[strtolower((string) $c['name'])] = $c;
        }

        foreach ($master as $name => $data) {
            $lower = strtolower((string) $name);
            $match = $currentFk[$lower] ?? null;

            if (! $match) {
                $match = array_find($currentFk, fn ($cData) => $this->adapter->constraintsMatch($data, (array) $cData));
                if ($match) {
                    $lower = strtolower((string) $match['name']);
                }
            }

            if (! $match) {
                $this->addChange("Database: missing constraint ($table/$name)", $this->addConstraintSql($table, $data));
            } elseif (! $this->adapter->constraintsMatch($data, (array) $match)) {
                $this->addChange("Database: incorrect constraint ($table/$name)", [...$this->dropConstraintSql($table, (string) $name), ...$this->addConstraintSql($table, $data)]);
            }

            unset($currentFk[$lower]);
        }

        foreach ($currentFk as $name => $data) {
            $this->addChange("Database: extra constraint ($table/$name)", $this->dropConstraintSql($table, (string) $name));
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return string[]
     */
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

    /**
     * @param  array<string, mixed>  $cdata
     * @return string[]
     */
    protected function addColumnSql(string $table, array $cdata, ?string $prev): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, function (Blueprint $b) use ($cdata, $prev): void {
            $col = $this->applyColumnToBlueprint($b, $cdata);
            if ($col) {
                empty($prev) ? $col->first() : $col->after($prev);
            }
        }));
    }

    /**
     * @param  array<string, mixed>  $cdata
     * @return string[]
     */
    protected function updateColumnSql(string $table, string $column, array $cdata): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, function (Blueprint $b) use ($cdata, $column): void {
            $col = $this->applyColumnToBlueprint($b, $cdata, $column);
            if ($col) {
                $col->change();
            }
        }));
    }

    /**
     * @return string[]
     */
    protected function dropColumnSql(string $table, string $column): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, fn (Blueprint $b) => $b->dropColumn($column)));
    }

    /**
     * @param  array<string, mixed>  $idata
     * @return string[]
     */
    protected function addIndexSql(string $table, array $idata): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, fn (Blueprint $b) => $this->applyIndexToBlueprint($b, $idata)));
    }

    /**
     * @param  array<string, mixed>  $idata
     * @return string[]
     */
    protected function updateIndexSql(string $table, string $name, array $idata): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, function (Blueprint $b) use ($name, $idata): void {
            $b->dropIndex($name);
            $this->applyIndexToBlueprint($b, $idata);
        }));
    }

    /**
     * @return string[]
     */
    protected function dropIndexSql(string $table, string $name): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, fn (Blueprint $b) => $b->dropIndex($name)));
    }

    /**
     * @return string[]
     */
    protected function dropTableSql(string $table): array
    {
        return $this->pretend(fn () => $this->db->getSchemaBuilder()->drop($table));
    }

    /**
     * @param  array<string, mixed>  $cdata
     */
    protected function applyColumnToBlueprint(Blueprint $b, array $cdata, ?string $old = null): ?\Illuminate\Database\Schema\ColumnDefinition
    {
        $type = (string) $cdata['Type'];
        $field = $old ?? $cdata['Field'];
        $unsigned = str_contains($type, 'unsigned');
        $clean = str_replace(' unsigned', '', $type);
        $params = [];

        if (preg_match('/^(\w+)\((.*)\)$/', $clean, $m)) {
            $clean = $m[1];
            $params = str_getcsv((string) $m[2], ',', "'", '\\');
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

        $col = ($method === 'enum') ? $b->enum((string) $field, (array) $params) : $b->{$method}((string) $field, ...$params);

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
            $b->renameColumn($old, (string) $cdata['Field']);
        }

        return $col;
    }

    /**
     * @param  array<string, mixed>  $idata
     */
    protected function applyIndexToBlueprint(Blueprint $b, array $idata): void
    {
        $cols = (array) $idata['Columns'];
        $name = (string) $idata['Name'];
        if ($name === 'PRIMARY') {
            $b->primary($cols);
        } elseif ($idata['Unique']) {
            $b->unique($cols, $name);
        } else {
            $b->index($cols, $name);
        }
    }

    /**
     * @param  array<string, mixed>  $c
     * @return string[]
     */
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

    /**
     * @return string[]
     */
    protected function dropConstraintSql(string $table, string $name): array
    {
        try {
            return $this->pretend(fn () => $this->db->getSchemaBuilder()->table($table, fn (Blueprint $b) => $b->dropForeign($name)));
        } catch (\Exception) {
            return (array) $this->adapter->dropConstraintSql($table, $name);
        }
    }

    /**
     * @return string[]
     */
    private function pretend(callable $callback): array
    {
        return array_map(fn ($q) => $q['query'] . ';', $this->db->pretend($callback));
    }

    /**
     * @param  array<string, mixed>  $c
     */
    protected function applyConstraintToBlueprint(Blueprint $b, array $c): void
    {
        $fk = $b->foreign((string) $c['foreign_key'], (string) $c['name'])->references((string) $c['key'])->on((string) $c['table']);
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
