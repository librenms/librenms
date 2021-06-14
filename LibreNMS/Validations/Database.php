<?php
/**
 * Database.php
 *
 * Checks the database for errors
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use LibreNMS\Config;
use LibreNMS\DB\Eloquent;
use LibreNMS\DB\Schema;
use LibreNMS\ValidationResult;
use LibreNMS\Validator;
use Symfony\Component\Yaml\Yaml;

class Database extends BaseValidation
{
    const MYSQL_MIN_VERSION = '5.7.7';
    const MYSQL_MIN_VERSION_DATE = 'March, 2021';
    const MYSQL_RECOMMENDED_VERSION = '8.0';

    const MARIADB_MIN_VERSION = '10.2.2';
    const MARIADB_MIN_VERSION_DATE = 'March, 2021';
    const MARIADB_RECOMMENDED_VERSION = '10.5';

    public function validate(Validator $validator)
    {
        if (! Eloquent::isConnected()) {
            return;
        }

        $this->validateSystem($validator);

        if ($this->checkSchemaVersion($validator)) {
            $this->checkSchema($validator);
            $this->checkCollation($validator);
        }
    }

    public function validateSystem(Validator $validator)
    {
        $this->checkVersion($validator);
        $this->checkMode($validator);
        $this->checkTime($validator);
        $this->checkMysqlEngine($validator);
    }

    private function checkSchemaVersion(Validator $validator): bool
    {
        $current = \LibreNMS\DB\Schema::getLegacySchema();
        $latest = 1000;

        if ($current === 0 || $current === $latest) {
            // Using Laravel migrations
            if (! Schema::isCurrent()) {
                $validator->fail('Your database is out of date!', './lnms migrate');

                return false;
            }

            $migrations = Schema::getUnexpectedMigrations();
            if ($migrations->isNotEmpty()) {
                $validator->warn('Your database schema has extra migrations (' . $migrations->implode(', ') .
                    '). If you just switched to the stable release from the daily release, your database is in between releases and this will be resolved with the next release.');
            }
        } elseif ($current < $latest) {
            $validator->fail(
                "Your database schema ($current) is older than the latest ($latest).",
                'Manually run ./daily.sh, and check for any errors.'
            );

            return false;
        } elseif ($current > $latest) {
            $validator->warn("Your database schema ($current) is newer than expected ($latest). If you just switched to the stable release from the daily release, your database is in between releases and this will be resolved with the next release.");
        }

        return true;
    }

    private function checkVersion(Validator $validator)
    {
        $version = Eloquent::DB()->selectOne('SELECT VERSION() as version')->version;
        $version = explode('-', $version);

        if (isset($version[1]) && $version[1] == 'MariaDB') {
            if (version_compare($version[0], self::MARIADB_MIN_VERSION, '<=')) {
                $validator->fail(
                    'MariaDB version ' . self::MARIADB_MIN_VERSION . ' is the minimum supported version as of ' .
                    self::MARIADB_MIN_VERSION_DATE . '.',
                    'Update MariaDB to a supported version, ' . self::MARIADB_RECOMMENDED_VERSION . ' suggested.'
                );
            }
        } else {
            if (version_compare($version[0], self::MYSQL_MIN_VERSION, '<=')) {
                $validator->fail(
                    'MySQL version ' . self::MYSQL_MIN_VERSION . ' is the minimum supported version as of ' .
                    self::MYSQL_MIN_VERSION_DATE . '.',
                    'Update MySQL to a supported version, ' . self::MYSQL_RECOMMENDED_VERSION . ' suggested.'
                );
            }
        }
    }

    private function checkTime(Validator $validator)
    {
        $raw_time = Eloquent::DB()->selectOne('SELECT NOW() as time')->time;
        $db_time = new Carbon($raw_time);
        $php_time = Carbon::now();

        $diff = $db_time->diffAsCarbonInterval($php_time);

        if ($diff->compare(CarbonInterval::minute(1)) > 0) {
            $message = "Time between this server and the mysql database is off\n";
            $message .= ' Mysql time ' . $db_time->toDateTimeString() . PHP_EOL;
            $message .= ' PHP time ' . $php_time->toDateTimeString() . PHP_EOL;

            $validator->fail($message);
        }
    }

    private function checkMode(Validator $validator)
    {
        // Test for lower case table name support
        $lc_mode = Eloquent::DB()->selectOne('SELECT @@global.lower_case_table_names as mode')->mode;
        if ($lc_mode != 0) {
            $validator->fail(
                'You have lower_case_table_names set to 1 or true in mysql config.',
                'Set lower_case_table_names=0 in your mysql config file in the [mysqld] section.'
            );
        }
    }

    private function checkMysqlEngine(Validator $validator)
    {
        $db = \config('database.connections.' . \config('database.default') . '.database');
        $query = "SELECT `TABLE_NAME` FROM information_schema.tables WHERE `TABLE_SCHEMA` = '$db' && `ENGINE` != 'InnoDB'";
        $tables = Eloquent::DB()->select($query);
        if (! empty($tables)) {
            $validator->result(
                ValidationResult::warn('Some tables are not using the recommended InnoDB engine, this may cause you issues.')
                    ->setList('Tables', array_column($tables, 'TABLE_NAME'))
            );
        }
    }

    private function checkCollation(Validator $validator)
    {
        $db_name = Eloquent::DB()->selectOne('SELECT DATABASE() as name')->name;

        // Test for correct character set and collation
        $db_collation_sql = "SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
            FROM information_schema.SCHEMATA S
            WHERE schema_name = '$db_name' AND
            ( DEFAULT_CHARACTER_SET_NAME != 'utf8mb4' OR DEFAULT_COLLATION_NAME != 'utf8mb4_unicode_ci')";
        $collation = Eloquent::DB()->selectOne($db_collation_sql);
        if (empty($collation) !== true) {
            $validator->fail(
                "MySQL Database collation is wrong: $collation->DEFAULT_CHARACTER_SET_NAME $collation->DEFAULT_COLLATION_NAME",
                'Check https://community.librenms.org/t/new-default-database-charset-collation/14956 for info on how to fix.'
            );
        }

        $table_collation_sql = "SELECT T.TABLE_NAME, C.CHARACTER_SET_NAME, C.COLLATION_NAME
            FROM information_schema.TABLES AS T, information_schema.COLLATION_CHARACTER_SET_APPLICABILITY AS C
            WHERE C.collation_name = T.table_collation AND T.table_schema = '$db_name' AND
             ( C.CHARACTER_SET_NAME != 'utf8mb4' OR C.COLLATION_NAME != 'utf8mb4_unicode_ci' );";
        $collation_tables = Eloquent::DB()->select($table_collation_sql);
        if (empty($collation_tables) !== true) {
            $result = ValidationResult::fail('MySQL tables collation is wrong: ')
                ->setFix('Check https://community.librenms.org/t/new-default-database-charset-collation/14956 for info on how to fix.')
                ->setList('Tables', array_map(function ($row) {
                    return "$row->TABLE_NAME   $row->CHARACTER_SET_NAME   $row->COLLATION_NAME";
                }, $collation_tables));
            $validator->result($result);
        }

        $column_collation_sql = "SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME
            FROM information_schema.COLUMNS  WHERE TABLE_SCHEMA = '$db_name' AND
            ( CHARACTER_SET_NAME != 'utf8mb4' OR COLLATION_NAME != 'utf8mb4_unicode_ci' );";
        $collation_columns = Eloquent::DB()->select($column_collation_sql);
        if (empty($collation_columns) !== true) {
            $result = ValidationResult::fail('MySQL column collation is wrong: ')
                ->setFix('Check https://community.librenms.org/t/new-default-database-charset-collation/14956 for info on how to fix.')
                ->setList('Columns', array_map(function ($row) {
                    return "$row->TABLE_NAME: $row->COLUMN_NAME   $row->CHARACTER_SET_NAME   $row->COLLATION_NAME";
                }, $collation_columns));
            $validator->result($result);
        }
    }

    private function checkSchema(Validator $validator)
    {
        $schema_file = Config::get('install_dir') . '/misc/db_schema.yaml';

        if (! is_file($schema_file)) {
            $validator->warn("We haven't detected the db_schema.yaml file");

            return;
        }

        $master_schema = Yaml::parse(file_get_contents($schema_file));
        $current_schema = Schema::dump();
        $schema_update = [];

        foreach ((array) $master_schema as $table => $data) {
            if (empty($current_schema[$table])) {
                $validator->fail("Database: missing table ($table)");
                $schema_update[] = $this->addTableSql($table, $data);
            } else {
                $current_columns = array_reduce($current_schema[$table]['Columns'], function ($array, $item) {
                    $array[$item['Field']] = $item;

                    return $array;
                }, []);

                foreach ($data['Columns'] as $index => $cdata) {
                    $column = $cdata['Field'];

                    // MySQL 8 fix, remove DEFAULT_GENERATED from timestamp extra columns
                    if ($cdata['Type'] == 'timestamp') {
                        $current_columns[$column]['Extra'] = preg_replace('/DEFAULT_GENERATED[ ]*/', '', $current_columns[$column]['Extra']);
                    }

                    if (empty($current_columns[$column])) {
                        $validator->fail("Database: missing column ($table/$column)");
                        $primary = false;
                        if ($data['Indexes']['PRIMARY']['Columns'] == [$column]) {
                            // include the primary index with the add statement
                            unset($data['Indexes']['PRIMARY']);
                            $primary = true;
                        }
                        $schema_update[] = $this->addColumnSql($table, $cdata, isset($data['Columns'][$index - 1]) ? $data['Columns'][$index - 1]['Field'] : null, $primary);
                    } elseif ($cdata !== $current_columns[$column]) {
                        $validator->fail("Database: incorrect column ($table/$column)");
                        $schema_update[] = $this->updateTableSql($table, $column, $cdata);
                    }

                    unset($current_columns[$column]); // remove checked columns
                }

                foreach ($current_columns as $column => $_unused) {
                    $validator->fail("Database: extra column ($table/$column)");
                    $schema_update[] = $this->dropColumnSql($table, $column);
                }

                $index_changes = [];
                if (isset($data['Indexes'])) {
                    foreach ($data['Indexes'] as $name => $index) {
                        if (empty($current_schema[$table]['Indexes'][$name])) {
                            $validator->fail("Database: missing index ($table/$name)");
                            $index_changes[] = $this->addIndexSql($table, $index);
                        } elseif ($index != $current_schema[$table]['Indexes'][$name]) {
                            $validator->fail("Database: incorrect index ($table/$name)");
                            $index_changes[] = $this->updateIndexSql($table, $name, $index);
                        }

                        unset($current_schema[$table]['Indexes'][$name]);
                    }
                }

                if (isset($current_schema[$table]['Indexes'])) {
                    foreach ($current_schema[$table]['Indexes'] as $name => $_unused) {
                        $validator->fail("Database: extra index ($table/$name)");
                        $schema_update[] = $this->dropIndexSql($table, $name);
                    }
                }
                $schema_update = array_merge($schema_update, $index_changes); // drop before create/update

                $constraint_changes = [];
                if (isset($data['Constraints'])) {
                    foreach ($data['Constraints'] as $name => $constraint) {
                        if (empty($current_schema[$table]['Constraints'][$name])) {
                            $validator->fail("Database: missing constraint ($table/$name)");
                            $constraint_changes[] = $this->addConstraintSql($table, $constraint);
                        } elseif ($constraint != $current_schema[$table]['Constraints'][$name]) {
                            $validator->fail("Database: incorrect constraint ($table/$name)");
                            $constraint_changes[] = $this->dropConstraintSql($table, $name);
                            $constraint_changes[] = $this->addConstraintSql($table, $constraint);
                        }

                        unset($current_schema[$table]['Constraints'][$name]);
                    }
                }

                if (isset($current_schema[$table]['Constraints'])) {
                    foreach ($current_schema[$table]['Constraints'] as $name => $_unused) {
                        $validator->fail("Database: extra constraint ($table/$name)");
                        $schema_update[] = $this->dropConstraintSql($table, $name);
                    }
                }
                $schema_update = array_merge($schema_update, $constraint_changes); // drop before create/update
            }

            unset($current_schema[$table]); // remove checked tables
        }

        foreach ($current_schema as $table => $data) {
            $validator->fail("Database: extra table ($table)");
            $schema_update[] = $this->dropTableSql($table);
        }

        if (empty($schema_update)) {
            $validator->ok('Database schema correct');
        } else {
            $result = ValidationResult::fail('We have detected that your database schema may be wrong, please report the following to us on Discord (https://t.libren.ms/discord) or the community site (https://t.libren.ms/5gscd):')
                ->setFix('Run the following SQL statements to fix.')
                ->setList('SQL Statements', $schema_update);
            $validator->result($result);
        }
    }

    private function addTableSql($table, $table_schema)
    {
        $columns = array_map([$this, 'columnToSql'], $table_schema['Columns']);
        $indexes = array_map([$this, 'indexToSql'], isset($table_schema['Indexes']) ? $table_schema['Indexes'] : []);

        $def = implode(', ', array_merge(array_values((array) $columns), array_values((array) $indexes)));

        return "CREATE TABLE `$table` ($def);";
    }

    private function addColumnSql($table, $schema, $previous_column, $primary = false)
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

    private function updateTableSql($table, $column, $column_schema)
    {
        return "ALTER TABLE `$table` CHANGE `$column` " . $this->columnToSql($column_schema) . ';';
    }

    private function dropColumnSql($table, $column)
    {
        return "ALTER TABLE `$table` DROP `$column`;";
    }

    private function addIndexSql($table, $index_schema)
    {
        return "ALTER TABLE `$table` ADD " . $this->indexToSql($index_schema) . ';';
    }

    private function updateIndexSql($table, $name, $index_schema)
    {
        return "ALTER TABLE `$table` DROP INDEX `$name`, " . $this->indexToSql($index_schema) . ';';
    }

    private function dropIndexSql($table, $name)
    {
        return "ALTER TABLE `$table` DROP INDEX `$name`;";
    }

    private function dropTableSql($table)
    {
        return "DROP TABLE `$table`;";
    }

    /**
     * Generate an SQL segment to create the column based on data from Schema::dump()
     *
     * @param array $column_data The array of data for the column
     * @return string sql fragment, for example: "`ix_id` int(10) unsigned NOT NULL"
     */
    private function columnToSql($column_data)
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
     * @param array $index_data The array of data for the index
     * @return string sql fragment, for example: "PRIMARY KEY (`device_id`)"
     */
    private function indexToSql($index_data)
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

    private function addConstraintSql($table, $constraint)
    {
        $sql = "ALTER TABLE `$table` ADD CONSTRAINT `{$constraint['name']}` FOREIGN KEY (`{$constraint['foreign_key']}`) ";
        $sql .= " REFERENCES `{$constraint['table']}` (`{$constraint['key']}`)";
        if (! empty($constraint['extra'])) {
            $sql .= ' ' . $constraint['extra'];
        }
        $sql .= ';';

        return $sql;
    }

    private function dropConstraintSql($table, $name)
    {
        return "ALTER TABLE `$table` DROP FOREIGN KEY `$name`;";
    }
}
