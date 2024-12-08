<?php
/*
 * CheckSchemaCollation.php
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

use LibreNMS\DB\Eloquent;
use LibreNMS\Interfaces\Validation;
use LibreNMS\Interfaces\ValidationFixer;
use LibreNMS\ValidationResult;

class CheckSchemaCollation implements Validation, ValidationFixer
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        $db_name = Eloquent::DB()->selectOne('SELECT DATABASE() as name')->name;

        // Test for correct character set and collation
        $db_collation_sql = "SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
            FROM information_schema.SCHEMATA S
            WHERE schema_name = '$db_name' AND
            ( DEFAULT_CHARACTER_SET_NAME != 'utf8mb4' OR DEFAULT_COLLATION_NAME != 'utf8mb4_unicode_ci')";
        $collation = Eloquent::DB()->selectOne($db_collation_sql);
        if (empty($collation) !== true) {
            return ValidationResult::fail(
                "MySQL Database collation is wrong: $collation->DEFAULT_CHARACTER_SET_NAME $collation->DEFAULT_COLLATION_NAME",
                'Check https://community.librenms.org/t/new-default-database-charset-collation/14956 for info on how to fix.'
            )->setFixer(__CLASS__);
        }

        $table_collation_sql = "SELECT T.TABLE_NAME, C.CHARACTER_SET_NAME, C.COLLATION_NAME
            FROM information_schema.TABLES AS T, information_schema.COLLATION_CHARACTER_SET_APPLICABILITY AS C
            WHERE C.collation_name = T.table_collation AND T.table_schema = '$db_name' AND
             ( C.CHARACTER_SET_NAME != 'utf8mb4' OR C.COLLATION_NAME != 'utf8mb4_unicode_ci' );";
        $collation_tables = Eloquent::DB()->select($table_collation_sql);
        if (empty($collation_tables) !== true) {
            return ValidationResult::fail('MySQL tables collation is wrong: ')
                ->setFix('Check https://community.librenms.org/t/new-default-database-charset-collation/14956 for info on how to fix.')
                ->setFixer(__CLASS__)
                ->setList('Tables', array_map(function ($row) {
                    return "$row->TABLE_NAME   $row->CHARACTER_SET_NAME   $row->COLLATION_NAME";
                }, $collation_tables));
        }

        $column_collation_sql = "SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME
            FROM information_schema.COLUMNS  WHERE TABLE_SCHEMA = '$db_name' AND
            ( CHARACTER_SET_NAME != 'utf8mb4' OR COLLATION_NAME != 'utf8mb4_unicode_ci' );";
        $collation_columns = Eloquent::DB()->select($column_collation_sql);
        if (empty($collation_columns) !== true) {
            return ValidationResult::fail('MySQL column collation is wrong: ')
                ->setFix('Check https://community.librenms.org/t/new-default-database-charset-collation/14956 for info on how to fix.')
                ->setFixer(__CLASS__)
                ->setList('Columns', array_map(function ($row) {
                    return "$row->TABLE_NAME: $row->COLUMN_NAME   $row->CHARACTER_SET_NAME   $row->COLLATION_NAME";
                }, $collation_columns));
        }

        return ValidationResult::ok(trans('validation.validations.database.CheckSchemaCollation.ok'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return Eloquent::isConnected() && CheckDatabaseSchemaVersion::isCurrent();
    }

    public function fix(): bool
    {
        \DB::table('migrations')->where('migration', '2021_02_09_122930_migrate_to_utf8mb4')->delete();
        $res = \Artisan::call('migrate', ['--force' => true, '--isolated' => true]);

        return $res === 0;
    }
}
