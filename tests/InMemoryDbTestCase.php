<?php

/*
 * InMemoryDbTestCase.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests;

use Illuminate\Contracts\Console\Kernel;

class InMemoryDbTestCase extends TestCase
{
    /** @var string */
    protected $connection = 'testing_memory';

    /**
     * Migrated in-memory database, shared by all tests in this process.
     * Each test runs inside a transaction that is rolled back afterwards.
     */
    private static ?\PDO $pdo = null;

    protected function setUp(): void
    {
        parent::setUp();

        $connection = $this->app->make('db')->connection($this->connection);

        if (self::$pdo === null) {
            $this->artisan('migrate:fresh', ['--database' => $this->connection]);
            $this->app[Kernel::class]->setArtisan(null);
            self::$pdo = $connection->getPdo();
        } else {
            $connection->setPdo(self::$pdo);
        }

        $connection->beginTransaction();
        $this->beforeApplicationDestroyed(function () use ($connection): void {
            $connection->rollBack(0);
        });

        $current = config('database.default');
        config(['database.default' => $this->connection]);
        \DB::purge($current);
    }
}
