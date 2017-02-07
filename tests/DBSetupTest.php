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
            self::$db_created = dbQuery("CREATE DATABASE " . $config['db_name']);
            dbQuery("SET GLOBAL sql_mode='ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
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
}
