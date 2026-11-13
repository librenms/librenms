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
use Illuminate\Foundation\Testing\DatabaseTransactions;

class InMemoryDbTestCase extends TestCase
{
    use DatabaseTransactions;

    /** @var string */
    protected $connection = 'testing_memory';

    /** @var string[] */
    protected $connectionsToTransact = ['testing_memory'];

    /**
     * Migrated in-memory database, shared by all tests in this process.
     * DatabaseTransactions rolls each test back, so it stays clean.
     */
    private static ?\PDO $pdo = null;

    /**
     * Migrate or restore the shared database before DatabaseTransactions
     * starts its transaction, so the transaction runs on the migrated PDO.
     */
    protected function setUpTraits()
    {
        $connection = $this->app->make('db')->connection($this->connection);

        if (self::$pdo === null) {
            $this->artisan('migrate:fresh', ['--database' => $this->connection]);
            $this->app[Kernel::class]->setArtisan(null);
            self::$pdo = $connection->getPdo();
        } else {
            $connection->setPdo(self::$pdo);
        }

        return parent::setUpTraits();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $current = config('database.default');
        config(['database.default' => $this->connection]);
        \DB::purge($current);
    }
}
