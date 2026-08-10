<?php

/**
 * CheckSchemaStructure.php
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
 * @link       http://librenms.org
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations\Database;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as LaravelSchema;
use LibreNMS\DB\Eloquent;
use LibreNMS\DB\Schema;
use LibreNMS\Interfaces\Validation;
use LibreNMS\Interfaces\ValidationFixer;
use LibreNMS\ValidationResult;
use Symfony\Component\Yaml\Yaml;

class CheckSchemaStructure implements Validation, ValidationFixer
{
    private array $descriptions = [];
    private array $schema_update = [];
    private readonly string $schema_file;

    public function __construct(?string $schema_file = null)
    {
        $this->schema_file = $schema_file ?? resource_path('definitions/schema/db_schema.yaml');
    }

    public function validate(): ValidationResult
    {
        if (! is_file($this->schema_file)) {
            return ValidationResult::warn("We haven't detected the db_schema.yaml file");
        }

        $this->checkSchema();
        if (empty($this->schema_update)) {
            return ValidationResult::ok('Database schema correct');
        }

        return ValidationResult::fail("We have detected that your database schema may be wrong\n" . implode("\n", $this->descriptions))
            ->setFix('Run the following SQL statements to fix it')
            ->setFixer(self::class)
            ->setList('SQL Statements', $this->schema_update);
    }

    public function fix(): bool
    {
        try {
            $this->checkSchema();
            foreach ($this->schema_update as $query) {
                DB::statement($query);
            }
        } catch (QueryException) {
            return false;
        }

        return true;
    }

    public function enabled(): bool
    {
        return Eloquent::isConnected() && CheckDatabaseSchemaVersion::isCurrent();
    }

    private function checkSchema(): void
    {
        $this->descriptions = [];
        $this->schema_update = [];

        $master = (array) Yaml::parse(file_get_contents($this->schema_file));
        $dbTables = collect(LaravelSchema::getTables())
            ->where('schema', DB::connection()->getDatabaseName())
            ->pluck('name')
            ->toArray();
        $current = Schema::dump(null, array_intersect(array_keys($master), $dbTables));

        foreach ($master as $table => $data) {
            if (empty($current[$table])) {
                $this->addChange("Database: missing table ($table)", $this->addTableSql($table, $data));
                continue;
            }

            $this->syncColumns($table, $data['Columns'], $current[$table]['Columns']);
            $this->syncIndexes($table, $data['Indexes'] ?? [], $current[$table]['Indexes'] ?? []);
            $this->syncConstraints($table, $data['Constraints'] ?? [], $current[$table]['Constraints'] ?? []);
        }

        foreach (array_diff($dbTables, array_keys($master)) as $table) {
            $this->addChange("Database: extra table ($table)", $this->dropTableSql($table));
        }

        if (preg_grep('/\d{4}-\d\d-\d\d \d\d:\d\d:\d\d/', $this->schema_update)) {
            array_unshift($this->schema_update, "SET TIME_ZONE='+00:00';");
        }
    }

    private function addChange(string $desc, string|array $sql): void
    {
        $this->descriptions[] = $desc;
        is_array($sql) ? $this->schema_update = [...$this->schema_update, ...$sql] : $this->schema_update[] = $sql;
    }

    private function syncColumns(string $table, array $master, array $current): void
    {
        $currentCols = array_change_key_case(array_column($current, null, 'Field'), CASE_LOWER);
        foreach ($master as $i => $cdata) {
            $field = $cdata['Field'];
            $lower = strtolower($field);
            if (! isset($currentCols[$lower])) {
                $this->addChange("Database: missing column ($table/$field)", $this->addColumnSql($table, $cdata, $master[$i - 1]['Field'] ?? null));
            } elseif (! $this->columnsMatch($cdata, $currentCols[$lower])) {
                $this->addChange("Database: incorrect column ($table/$field)", $this->updateTableSql($table, $field, $cdata));
            }
            unset($currentCols[$lower]);
        }
        foreach ($currentCols as $c) {
            $this->addChange("Database: extra column ($table/{$c['Field']})", $this->dropColumnSql($table, $c['Field']));
        }
    }

    private function columnsMatch(array $master, array $current): bool
    {
        $typeMatch = ($master['Type'] === $current['Type']) || ($master['Type'] === 'json' && in_array($current['Type'], ['json', 'longtext', 'text']));

        return $typeMatch && $master['Null'] == $current['Null'] && ($master['Default'] ?? null) == ($current['Default'] ?? null) && $master['Extra'] == $current['Extra'];
    }

    private function syncIndexes(string $table, array $master, array $current): void
    {
        $currentIdx = array_change_key_case($current, CASE_LOWER);
        foreach ($master as $name => $data) {
            $lower = strtolower($name);
            if (! isset($currentIdx[$lower])) {
                $this->addChange("Database: missing index ($table/$name)", $this->addIndexSql($table, $data));
            } else {
                $c = $currentIdx[$lower];
                $colsMatch = array_map(strtolower(...), $data['Columns']) === array_map(strtolower(...), $c['Columns']);
                if (! $colsMatch || $data['Unique'] != $c['Unique']) {
                    $this->addChange("Database: incorrect index ($table/$name)", $this->updateIndexSql($table, $name, $data));
                }
            }
            unset($currentIdx[$lower]);
        }
        foreach ($currentIdx as $name => $_) {
            $this->addChange("Database: extra index ($table/$name)", $this->dropIndexSql($table, $name));
        }
    }

    private function syncConstraints(string $table, array $master, array $current): void
    {
        $currentFk = array_change_key_case($current, CASE_LOWER);
        foreach ($master as $name => $data) {
            $lower = strtolower($name);
            if (! isset($currentFk[$lower])) {
                $this->addChange("Database: missing constraint ($table/$name)", $this->addConstraintSql($table, $data));
            } elseif ($data != $currentFk[$lower]) {
                $this->addChange("Database: incorrect constraint ($table/$name)", [$this->dropConstraintSql($table, $name), $this->addConstraintSql($table, $data)]);
            }
            unset($currentFk[$lower]);
        }
        foreach ($currentFk as $name => $_) {
            $this->addChange("Database: extra constraint ($table/$name)", $this->dropConstraintSql($table, $name));
        }
    }

    private function addTableSql(string $table, array $data): string
    {
        $queries = DB::pretend(fn () => LaravelSchema::create($table, function (Blueprint $b) use ($data): void {
            foreach ($data['Columns'] as $c) {
                $this->applyColumnToBlueprint($b, $c);
            }
            foreach ($data['Indexes'] ?? [] as $i) {
                $this->applyIndexToBlueprint($b, $i);
            }
        }));

        return implode("\n", array_column($queries, 'query')) . ';';
    }

    private function addColumnSql(string $table, array $cdata, ?string $prev): array
    {
        $queries = DB::pretend(fn () => LaravelSchema::table($table, function (Blueprint $b) use ($cdata, $prev): void {
            $col = $this->applyColumnToBlueprint($b, $cdata);
            empty($prev) ? $col->first() : $col->after($prev);
        }));

        return array_map(fn ($q) => $q['query'] . ';', $queries);
    }

    private function updateTableSql(string $table, string $column, array $cdata): array
    {
        $queries = DB::pretend(fn () => LaravelSchema::table($table, fn (Blueprint $b) => $this->applyColumnToBlueprint($b, $cdata, $column)->change()));

        return array_map(fn ($q) => $q['query'] . ';', $queries);
    }

    private function dropColumnSql(string $table, string $column): string
    {
        $queries = DB::pretend(fn () => LaravelSchema::table($table, fn (Blueprint $b) => $b->dropColumn($column)));

        return $queries[0]['query'] . ';';
    }

    private function addIndexSql(string $table, array $idata): string
    {
        $queries = DB::pretend(fn () => LaravelSchema::table($table, fn (Blueprint $b) => $this->applyIndexToBlueprint($b, $idata)));

        return $queries[0]['query'] . ';';
    }

    private function updateIndexSql(string $table, string $name, array $idata): array
    {
        $queries = DB::pretend(fn () => LaravelSchema::table($table, function (Blueprint $b) use ($name, $idata): void {
            $b->dropIndex($name);
            $this->applyIndexToBlueprint($b, $idata);
        }));

        return array_map(fn ($q) => $q['query'] . ';', $queries);
    }

    private function dropIndexSql(string $table, string $name): string
    {
        $queries = DB::pretend(fn () => LaravelSchema::table($table, fn (Blueprint $b) => $b->dropIndex($name)));

        return $queries[0]['query'] . ';';
    }

    private function dropTableSql(string $table): string
    {
        $queries = DB::pretend(fn () => LaravelSchema::drop($table));

        return $queries[0]['query'] . ';';
    }

    private function applyColumnToBlueprint(Blueprint $b, array $cdata, ?string $old = null): ?\Illuminate\Database\Schema\ColumnDefinition
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
            'varchar' => 'string', 'blob' => 'binary', 'mediumblob' => 'mediumBinary', 'longblob' => 'longBinary', 'datetime' => 'dateTime', default => $clean,
        };

        if (! method_exists($b, $method)) {
            return null;
        }
        $col = $b->{$method}($field, ...$params);
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

    private function applyIndexToBlueprint(Blueprint $b, array $idata): void
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

    private function addConstraintSql(string $table, array $c): string
    {
        $queries = DB::pretend(fn () => LaravelSchema::table($table, function (Blueprint $b) use ($c): void {
            $fk = $b->foreign($c['foreign_key'], $c['name'])->references($c['key'])->on($c['table']);
            if (str_contains(strtoupper((string) ($c['extra'] ?? '')), 'ON DELETE CASCADE')) {
                $fk->onDelete('cascade');
            } elseif (str_contains(strtoupper((string) ($c['extra'] ?? '')), 'ON DELETE SET NULL')) {
                $fk->onDelete('set null');
            }
            if (str_contains(strtoupper((string) ($c['extra'] ?? '')), 'ON UPDATE CASCADE')) {
                $fk->onUpdate('cascade');
            }
        }));

        return $queries[0]['query'] . ';';
    }

    private function dropConstraintSql(string $table, string $name): string
    {
        $queries = DB::pretend(fn () => LaravelSchema::table($table, fn (Blueprint $b) => $b->dropForeign($name)));

        return $queries[0]['query'] . ';';
    }
}
