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

use PHPUnit_Framework_ExpectationFailedException as PHPUnitException;

class DBSetupTest extends \PHPUnit_Framework_TestCase
{

    private static $schema;
    private static $sql_mode;
    private static $db_created;
    protected $backupGlobals = false;

    public static function setUpBeforeClass()
    {
        if (getenv('DBTEST')) {
            global $config;
            self::$sql_mode = dbFetchCell("SELECT @@global.sql_mode as sql_mode");
            dbQuery("SET NAMES 'utf8'");
            dbQuery("SET CHARACTER SET 'utf8'");
            dbQuery("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
            self::$db_created = dbQuery("CREATE DATABASE " . $config['db_name'] . " CHARACTER SET utf8 COLLATE utf8_unicode_ci");
            dbQuery("SET GLOBAL sql_mode='ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
            dbQuery("USE " . $config['db_name']);
            $build_base = $config['install_dir'] . '/build-base.php';
            exec($build_base, $schema);
            self::$schema = $schema;
        }
    }

    public static function tearDownAfterClass()
    {
        if (getenv('DBTEST')) {
            global $config;

            dbQuery("SET GLOBAL sql_mode='" . self::$sql_mode . "'");
            if (self::$db_created) {
                dbQuery("DROP DATABASE " . $config['db_name']);
            }
        }
    }

    public function testSetupDB()
    {
        if (getenv('DBTEST')) {
            foreach (self::$schema as $output) {
                if (preg_match('/([1-9]+) errors/', $output) || preg_match('/Cannot execute query/', $output)) {
                    throw new PHPUnitException("Errors loading DB Schema: " . $output);
                }
            }
        }
    }

    public function testCheckDBCollation()
    {
        global $config;
        if (getenv('DBTEST')) {
            $collation = dbFetchRows("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA S WHERE schema_name = '" . $config['db_name'] . "' AND  ( DEFAULT_CHARACTER_SET_NAME != 'utf8' OR DEFAULT_COLLATION_NAME != 'utf8_unicode_ci')");
            if (isset($collation[0])) {
                $error = implode(' ', $collation[0]);
            } else {
                $error = '';
            }
            $this->assertEmpty($collation, 'Wrong Database Collation or Character set: ' . $error);
        }
    }

    public function testCheckTableCollation()
    {
        global $config;
        if (getenv('DBTEST')) {
            $collation = dbFetchRows("SELECT T.TABLE_NAME, C.CHARACTER_SET_NAME, C.COLLATION_NAME FROM information_schema.TABLES AS T, information_schema.COLLATION_CHARACTER_SET_APPLICABILITY AS C WHERE C.collation_name = T.table_collation AND T.table_schema = '" . $config['db_name'] . "' AND  ( C.CHARACTER_SET_NAME != 'utf8' OR C.COLLATION_NAME != 'utf8_unicode_ci' );");
            $error = '';
            foreach ($collation as $id => $data) {
                $error .= implode(' ', $data) . PHP_EOL;
            }
            $this->assertEmpty($collation, 'Wrong Table Collation or Character set: ' . $error);
        }
    }

    public function testCheckColumnCollation()
    {
        global $config;
        if (getenv('DBTEST')) {
            $collation = dbFetchRows("SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS  WHERE TABLE_SCHEMA = '" . $config['db_name'] . "'  AND  ( CHARACTER_SET_NAME != 'utf8' OR COLLATION_NAME != 'utf8_unicode_ci' );");
            $error = '';
            foreach ($collation as $id => $data) {
                $error .= implode(' ', $data) . PHP_EOL;
            }
            $this->assertEmpty($collation, 'Wrong Column Collation or Character set: ' . $error);
        }
    }
}
