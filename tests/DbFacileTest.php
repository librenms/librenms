<?php

/**
 * DbFacileTest.php
 *
 * Tests that dbFacile recovers from a lost database connection
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
 *
 * @copyright  2026 Jacob Wilkins
 * @author     Jacob Wilkins <jacob@9.nz>
 */

namespace LibreNMS\Tests;

use App\Models\Device;
use Illuminate\Support\Facades\DB;

/**
 * Long lived processes such as syslog.php hold a single connection for their whole
 * lifetime.  When that connection dies, every subsequent query must still succeed,
 * or the process silently stops recording data until it is restarted.
 *
 * These tests deliberately do not use DatabaseTransactions: Connection::handleQueryException()
 * rethrows without reconnecting while a transaction is open, so the reconnect path
 * would be unreachable.  They only read, so there is nothing to roll back.
 */
final class DbFacileTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'])) {
            $this->markTestSkipped('Killing a connection from a second session requires MySQL/MariaDB.');
        }
    }

    public function testFetchRowsReconnectsAfterLostConnection(): void
    {
        $before = $this->killCurrentConnection();

        $this->assertSame([['one' => 1]], dbFetchRows('SELECT 1 AS `one`'));
        $this->assertNotSame($before, $this->connectionId(), 'Expected a new connection, not the killed one.');
    }

    public function testFetchRowReconnectsAfterLostConnection(): void
    {
        $before = $this->killCurrentConnection();

        $this->assertSame(['one' => 1], dbFetchRow('SELECT 1 AS `one`'));
        $this->assertNotSame($before, $this->connectionId(), 'Expected a new connection, not the killed one.');
    }

    public function testFetchCellReconnectsAfterLostConnection(): void
    {
        $before = $this->killCurrentConnection();

        $this->assertSame(1, dbFetchCell('SELECT 1 AS `one`'));
        $this->assertNotSame($before, $this->connectionId(), 'Expected a new connection, not the killed one.');
    }

    public function testDeviceLookupSurvivesLostConnection(): void
    {
        $device = Device::factory()->create(); /** @var Device $device */
        try {
            $this->killCurrentConnection();

            // the lookup includes/syslog.php performs for every message it receives
            $this->assertSame(
                $device->device_id,
                dbFetchCell('SELECT `device_id` FROM `devices` WHERE `hostname` = ? OR `sysName` = ?', [$device->hostname, $device->hostname])
            );
        } finally {
            $device->delete();
        }
    }

    /**
     * Kill this connection from a second session, leaving a stale handle behind.
     * Returns the id of the connection that was killed.
     */
    private function killCurrentConnection(): int
    {
        $connection = DB::connection();
        $id = $this->connectionId();

        $killer = app('db.factory')->make($connection->getConfig(), 'dbfacile_test_killer');
        $killer->unprepared('KILL ' . $id);
        $killer->disconnect();

        return $id;
    }

    private function connectionId(): int
    {
        return (int) DB::connection()->getPdo()->query('SELECT CONNECTION_ID()')->fetchColumn();
    }
}
