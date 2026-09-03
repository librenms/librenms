<?php

/**
 * SyslogLostConnectionTest.php
 *
 * Tests that syslog processing recovers from a lost database connection
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

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Syslog;
use Illuminate\Support\Facades\DB;
use LibreNMS\Syslog\Processor;

/**
 * syslog.php is started once by syslog-ng's program() destination and holds a single
 * database connection for its whole lifetime.  When that connection dies, every
 * message that arrives afterwards must still be recorded, or the receiver silently
 * stops storing syslog until someone restarts it.
 *
 * These tests deliberately do not use DatabaseTransactions: Connection::handleQueryException()
 * rethrows without reconnecting while a transaction is open, so the reconnect path
 * would be unreachable.  Rows they create are removed in a finally block instead.
 */
final class SyslogLostConnectionTest extends DBTestCase
{
    private Processor $processor;

    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'])) {
            $this->markTestSkipped('Killing a connection from a second session requires MySQL/MariaDB.');
        }

        $this->processor = new Processor;
        DeviceCache::flush();
    }

    protected function tearDown(): void
    {
        DeviceCache::flush();

        parent::tearDown();
    }

    public function testDeviceLookupSurvivesLostConnection(): void
    {
        /** @var Device $device */
        $device = Device::factory()->create(['os' => 'generic', 'version' => '1.2.3']);

        try {
            $killed = $this->killCurrentConnection();

            $entry = $this->processor->process($this->message($device->hostname), false);

            $this->assertEquals($device->device_id, $entry['device_id'], 'Expected the lookup to survive the lost connection.');
            $this->assertNotSame($killed, $this->connectionId(), 'Expected a new connection, not the killed one.');
        } finally {
            $device->delete();
        }
    }

    public function testMessageIsStoredAfterLostConnection(): void
    {
        /** @var Device $device */
        $device = Device::factory()->create(['os' => 'generic']);

        try {
            $killed = $this->killCurrentConnection();

            $this->processor->process($this->message($device->hostname));

            $this->assertSame(1, Syslog::where('device_id', $device->device_id)->count());
            $this->assertNotSame($killed, $this->connectionId(), 'Expected a new connection, not the killed one.');
        } finally {
            Syslog::where('device_id', $device->device_id)->delete();
            $device->delete();
        }
    }

    /**
     * @return array<string, string>
     */
    private function message(string $host): array
    {
        return [
            'host' => $host,
            'facility' => 'local7',
            'priority' => 'info',
            'level' => 'info',
            'tag' => '0e',
            'timestamp' => '2024-01-01 00:00:00',
            'msg' => 'lost connection test',
            'program' => 'TEST',
        ];
    }

    /**
     * Kill this connection from a second session, leaving a stale handle behind.
     * Returns the id of the connection that was killed.
     */
    private function killCurrentConnection(): int
    {
        $id = $this->connectionId();

        $killer = app('db.factory')->make(DB::connection()->getConfig(), 'syslog_test_killer');
        $killer->unprepared('KILL ' . $id);
        $killer->disconnect();

        return $id;
    }

    private function connectionId(): int
    {
        return (int) DB::connection()->getPdo()->query('SELECT CONNECTION_ID()')->fetchColumn();
    }
}
