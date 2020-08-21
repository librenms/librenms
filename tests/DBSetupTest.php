<?php
/**
 * DBSetup.php
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <librenms+n@laf.io>
 */

namespace LibreNMS\Tests;

use Artisan;
use DB;

class DBSetupTest extends DBTestCase
{
    protected $db_name;
    protected $connection = 'testing';

    protected function setUp(): void
    {
        parent::setUp();
        $this->db_name = DB::connection($this->connection)->getDatabaseName();
    }

    public function testSetupDB()
    {
        $result = Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--env' => 'testing',
            '--database' => $this->connection
        ]);

        $this->assertSame(0, $result, "Errors loading DB Schema: " . Artisan::output());
    }

    public function testSchemaFiles()
    {
        $files = glob(base_path('/sql-schema/*.sql'));
        $this->assertCount(282, $files, 'You should not create new legacy schema files.');
    }

    public function testSchema()
    {
        $files = array_map(function ($migration_file) {
            return basename($migration_file, '.php');
        }, array_diff(scandir(base_path('/database/migrations')), ['.', '..']));
        $migrated = DB::connection($this->connection)->table('migrations')->pluck('migration')->toArray();
        sort($files);
        sort($migrated);
        $this->assertEquals($files, $migrated, "List of run migrations did not match existing migration files.");

        // check legacy schema version is 1000
        $schema = DB::connection($this->connection)->table('dbSchema')
            ->orderBy('version', 'DESC')
            ->value('version');
        $this->assertEquals(1000, $schema, "Seed not run, after seed legacy dbSchema should be 1000");
    }

    public function testCheckDBCollation()
    {
        $collation = DB::connection($this->connection)->select(DB::raw("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA S WHERE schema_name = '$this->db_name' AND  ( DEFAULT_CHARACTER_SET_NAME != 'utf8' OR DEFAULT_COLLATION_NAME != 'utf8_unicode_ci')"));
        if (isset($collation[0])) {
            $error = implode(' ', $collation[0]);
        } else {
            $error = '';
        }
        $this->assertEmpty($collation, 'Wrong Database Collation or Character set: ' . $error);
    }

    public function testCheckTableCollation()
    {
        $collation = DB::connection($this->connection)->select(DB::raw("SELECT T.TABLE_NAME, C.CHARACTER_SET_NAME, C.COLLATION_NAME FROM information_schema.TABLES AS T, information_schema.COLLATION_CHARACTER_SET_APPLICABILITY AS C WHERE C.collation_name = T.table_collation AND T.table_schema = '$this->db_name' AND  ( C.CHARACTER_SET_NAME != 'utf8' OR C.COLLATION_NAME != 'utf8_unicode_ci' );"));
        $error = '';
        foreach ($collation as $id => $data) {
            $error .= implode(' ', $data) . PHP_EOL;
        }
        $this->assertEmpty($collation, 'Wrong Table Collation or Character set: ' . $error);
    }

    public function testCheckColumnCollation()
    {
        $collation = DB::connection($this->connection)->select(DB::raw("SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS  WHERE TABLE_SCHEMA = '$this->db_name'  AND  ( CHARACTER_SET_NAME != 'utf8' OR COLLATION_NAME != 'utf8_unicode_ci' );"));
        $error = '';
        foreach ($collation as $id => $data) {
            $error .= implode(' ', $data) . PHP_EOL;
        }
        $this->assertEmpty($collation, 'Wrong Column Collation or Character set: ' . $error);
    }

    public function testSqlMode()
    {
        $this->assertEquals(
            'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION',
            DB::connection($this->connection)->select(DB::raw("SELECT @@sql_mode AS mode"))[0]->mode
        );
    }

    public function testValidateSchema()
    {
        if (is_file('misc/db_schema.yaml')) {
            $master_schema = \Symfony\Component\Yaml\Yaml::parse(
                file_get_contents('misc/db_schema.yaml')
            );

            $current_schema = dump_db_schema($this->connection);

            $message = "Schema does not match the expected schema defined by misc/db_schema.yaml\n";
            $message .= "If you have changed the schema, make sure you update it with ./scripts/build-schema.php\n";

            $this->assertEquals($master_schema, $current_schema, $message);
        }
    }
}
