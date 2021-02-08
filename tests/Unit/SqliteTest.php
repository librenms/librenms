<?php
/**
 * SqliteTest.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use Artisan;
use Illuminate\Database\QueryException;
use LibreNMS\Tests\TestCase;

class SqliteTest extends TestCase
{
    private $connection = 'testing_memory';

    public function testMigrationsRunWithoutError()
    {
        try {
            $result = Artisan::call('migrate', ['--database' => $this->connection, '--seed' => true]);
            $output = Artisan::output();

            $this->assertEquals(0, $result, "SQLite migration failed:\n$output");
            $this->assertNotEmpty($output, 'Migrations not run');
        } catch (QueryException $queryException) {
            preg_match('/Migrating: (\w+)$/', Artisan::output(), $matches);
            $migration = $matches[1] ?? '?';
            $output = isset($matches[1]) ? '' : "\n\n" . Artisan::output();
            $this->fail($queryException->getMessage() . $output . "\n\nCould not run migration {$migration}) on SQLite");
        }

        $count = \DB::connection($this->connection)->table('alert_templates')->count();
        $this->assertGreaterThan(0, $count, 'Database content check failed.');
    }
}
