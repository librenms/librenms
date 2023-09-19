<?php
/*
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
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations\Database;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use LibreNMS\Config;
use LibreNMS\DB\Eloquent;
use LibreNMS\DB\Schema;
use LibreNMS\Interfaces\Validation;
use LibreNMS\Interfaces\ValidationFixer;
use LibreNMS\ValidationResult;
use Symfony\Component\Yaml\Yaml;

class CheckSchemaStructure implements Validation, ValidationFixer
{
    /** @var array */
    private $descriptions = [];
    /** @var array */
    private $schema_update = [];
    /** @var string */
    private $schema_file;

    public function __construct()
    {
        $this->schema_file = Config::get('install_dir') . '/misc/db_schema.yaml';
    }

    /**
     * @inheritDoc
     */
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
            ->setFixer(__CLASS__)
            ->setList('SQL Statements', $this->schema_update);
    }

    public function fix(): bool
    {
        try {
            $this->checkSchema();

            foreach ($this->schema_update as $query) {
                DB::statement($query);
            }
        } catch (QueryException $e) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return Eloquent::isConnected() && CheckDatabaseSchemaVersion::isCurrent();
    }

    private function checkSchema(): void
    {
        $master_schema = Yaml::parse(file_get_contents($this->schema_file));
        $current_schema = Schema::dump();

        foreach ((array) $master_schema as $table => $data) {
            if (empty($current_schema[$table])) {
                $this->descriptions[] = "Database: missing table ($table)";
                $this->schema_update[] = $this->addTableSql($table, $data);
            } else {
                $current_columns = array_reduce($current_schema[$table]['Columns'], function ($array, $item) {
                    $array[$item['Field']] = $item;

                    return $array;
                }, []);

                foreach ($data['Columns'] as $index => $cdata) {
                    $column = $cdata['Field'];

                    // MySQL 8 fix, remove DEFAULT_GENERATED from timestamp extra columns
                    if ($cdata['Type'] == 'timestamp') {
                        $current_columns[$column]['Extra'] = preg_replace('/DEFAULT_GENERATED */', '', $current_columns[$column]['Extra']);
                    }

                    if (empty($current_columns[$column])) {
                        $this->descriptions[] = "Database: missing column ($table/$column)";
                        $primary = false;
                        if ($data['Indexes']['PRIMARY']['Columns'] == [$column]) {
                            // include the primary index with the add statement
                            unset($data['Indexes']['PRIMARY']);
                            $primary = true;
                        }
                        $this->schema_update[] = $this->addColumnSql($table, $cdata, isset($data['Columns'][$index - 1]) ? $data['Columns'][$index - 1]['Field'] : null, $primary);
                    } elseif ($cdata !== $current_columns[$column]) {
                        $this->descriptions[] = "Database: incorrect column ($table/$column)";
                        $this->schema_update[] = $this->updateTableSql($table, $column, $cdata);
                    }

                    unset($current_columns[$column]); // remove checked columns
                }

                foreach ($current_columns as $column => $_unused) {
                    $this->descriptions[] = "Database: extra column ($table/$column)";
                    $this->schema_update[] = $this->dropColumnSql($table, $column);
                }

                $index_changes = [];
                if (isset($data['Indexes'])) {
                    foreach ($data['Indexes'] as $name => $index) {
                        if (empty($current_schema[$table]['Indexes'][$name])) {
                            $this->descriptions[] = "Database: missing index ($table/$name)";
                            $index_changes[] = $this->addIndexSql($table, $index);
                        } elseif ($index != $current_schema[$table]['Indexes'][$name]) {
                            $this->descriptions[] = "Database: incorrect index ($table/$name)";
                            $index_changes[] = $this->updateIndexSql($table, $name, $index);
                        }

                        unset($current_schema[$table]['Indexes'][$name]);
                    }
                }

                if (isset($current_schema[$table]['Indexes'])) {
                    foreach ($current_schema[$table]['Indexes'] as $name => $_unused) {
                        $this->descriptions[] = "Database: extra index ($table/$name)";
                        $this->schema_update[] = $this->dropIndexSql($table, $name);
                    }
                }
                $this->schema_update = array_merge($this->schema_update, $index_changes); // drop before create/update

                $constraint_changes = [];
                if (isset($data['Constraints'])) {
                    foreach ($data['Constraints'] as $name => $constraint) {
                        if (empty($current_schema[$table]['Constraints'][$name])) {
                            $this->descriptions[] = "Database: missing constraint ($table/$name)";
                            $constraint_changes[] = $this->addConstraintSql($table, $constraint);
                        } elseif ($constraint != $current_schema[$table]['Constraints'][$name]) {
                            $this->descriptions[] = "Database: incorrect constraint ($table/$name)";
                            $constraint_changes[] = $this->dropConstraintSql($table, $name);
                            $constraint_changes[] = $this->addConstraintSql($table, $constraint);
                        }

                        unset($current_schema[$table]['Constraints'][$name]);
                    }
                }

                if (isset($current_schema[$table]['Constraints'])) {
                    foreach ($current_schema[$table]['Constraints'] as $name => $_unused) {
                        $this->descriptions[] = "Database: extra constraint ($table/$name)";
                        $this->schema_update[] = $this->dropConstraintSql($table, $name);
                    }
                }
                $this->schema_update = array_merge($this->schema_update, $constraint_changes); // drop before create/update
            }

            unset($current_schema[$table]); // remove checked tables
        }

        foreach ($current_schema as $table => $data) {
            $this->descriptions[] = "Database: extra table ($table)";
            $this->schema_update[] = $this->dropTableSql($table);
        }

        // set utc timezone if timestamp issues
        if (preg_grep('/\d{4}-\d\d-\d\d \d\d:\d\d:\d\d/', $this->schema_update)) {
            array_unshift($this->schema_update, "SET TIME_ZONE='+00:00';");
        }
    }

    private function addTableSql(string $table, array $table_schema): string
    {
        $columns = array_map([$this, 'columnToSql'], $table_schema['Columns']);
        $indexes = array_map([$this, 'indexToSql'], $table_schema['Indexes'] ?? []);

        $def = implode(', ', array_merge(array_values($columns), array_values($indexes)));

        return "CREATE TABLE `$table` ($def);";
    }

    private function addColumnSql(string $table, array $schema, ?string $previous_column, bool $primary = false): string
    {
        $sql = "ALTER TABLE `$table` ADD " . $this->columnToSql($schema);
        if ($primary) {
            $sql .= ' PRIMARY KEY';
        }
        if (empty($previous_column)) {
            $sql .= ' FIRST';
        } else {
            $sql .= " AFTER `$previous_column`";
        }

        return $sql . ';';
    }

    private function updateTableSql(string $table, string $column, array $column_schema): string
    {
        return "ALTER TABLE `$table` CHANGE `$column` " . $this->columnToSql($column_schema) . ';';
    }

    private function dropColumnSql(string $table, string $column): string
    {
        return "ALTER TABLE `$table` DROP `$column`;";
    }

    private function addIndexSql(string $table, array $index_schema): string
    {
        return "ALTER TABLE `$table` ADD " . $this->indexToSql($index_schema) . ';';
    }

    private function updateIndexSql(string $table, string $name, array $index_schema): string
    {
        return "ALTER TABLE `$table` DROP INDEX `$name`, " . $this->indexToSql($index_schema) . ';';
    }

    private function dropIndexSql(string $table, string $name): string
    {
        return "ALTER TABLE `$table` DROP INDEX `$name`;";
    }

    private function dropTableSql(string $table): string
    {
        return "DROP TABLE `$table`;";
    }

    /**
     * Generate an SQL segment to create the column based on data from Schema::dump()
     *
     * @param  array  $column_data  The array of data for the column
     * @return string sql fragment, for example: "`ix_id` int(10) unsigned NOT NULL"
     */
    private function columnToSql(array $column_data): string
    {
        $segments = ["`${column_data['Field']}`", $column_data['Type']];

        $segments[] = $column_data['Null'] ? 'NULL' : 'NOT NULL';

        if (isset($column_data['Default'])) {
            if ($column_data['Default'] === 'CURRENT_TIMESTAMP') {
                $segments[] = 'DEFAULT CURRENT_TIMESTAMP';
            } elseif ($column_data['Default'] == 'NULL') {
                $segments[] = 'DEFAULT NULL';
            } else {
                $segments[] = "DEFAULT '${column_data['Default']}'";
            }
        }

        if ($column_data['Extra'] == 'on update current_timestamp()') {
            $segments[] = 'on update CURRENT_TIMESTAMP';
        } else {
            $segments[] = $column_data['Extra'];
        }

        return implode(' ', $segments);
    }

    /**
     * Generate an SQL segment to create the index based on data from Schema::dump()
     *
     * @param  array  $index_data  The array of data for the index
     * @return string sql fragment, for example: "PRIMARY KEY (`device_id`)"
     */
    private function indexToSql(array $index_data): string
    {
        if ($index_data['Name'] == 'PRIMARY') {
            $index = 'PRIMARY KEY (%s)';
        } elseif ($index_data['Unique']) {
            $index = "UNIQUE `{$index_data['Name']}` (%s)";
        } else {
            $index = "INDEX `{$index_data['Name']}` (%s)";
        }

        $columns = implode(',', array_map(function ($col) {
            return "`$col`";
        }, $index_data['Columns']));

        return sprintf($index, $columns);
    }

    private function addConstraintSql(string $table, array $constraint): string
    {
        $sql = "ALTER TABLE `$table` ADD CONSTRAINT `{$constraint['name']}` FOREIGN KEY (`{$constraint['foreign_key']}`) ";
        $sql .= " REFERENCES `{$constraint['table']}` (`{$constraint['key']}`)";
        if (! empty($constraint['extra'])) {
            $sql .= ' ' . $constraint['extra'];
        }
        $sql .= ';';

        return $sql;
    }

    private function dropConstraintSql(string $table, string $name): string
    {
        return "ALTER TABLE `$table` DROP FOREIGN KEY `$name`;";
    }
}
