<?php
/**
 * GraphiteStoreTest.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Data;

use Carbon\Carbon;
use LibreNMS\Data\Store\Graphite;
use LibreNMS\Tests\TestCase;

/**
 * @group datastores
 */
class GraphiteStoreTest extends TestCase
{
    protected $timestamp = 997464400;

    protected function setUp(): void
    {
        parent::setUp();

        // fix the date
        Carbon::setTestNow(Carbon::createFromTimestamp($this->timestamp));
    }

    protected function tearDown(): void
    {
        // restore Carbon:now() to normal
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function testSocketConnectError()
    {
        $mockFactory = \Mockery::mock(\Socket\Raw\Factory::class);

        $mockFactory->shouldReceive('createClient')
            ->andThrow('Socket\Raw\Exception', 'Failed to handle connect exception');

        new Graphite($mockFactory);
    }

    public function testSocketWriteError()
    {
        $mockSocket = \Mockery::mock(\Socket\Raw\Socket::class);
        $graphite = $this->mockGraphite($mockSocket);

        $mockSocket->shouldReceive('write')
            ->andThrow('Socket\Raw\Exception', 'Did not handle socket exception');

        $graphite->put(['hostname' => 'test'], 'fake', ['rrd_name' => 'name'], ['one' => 1]);
    }

    public function testSimpleWrite()
    {
        $mockSocket = \Mockery::mock(\Socket\Raw\Socket::class);
        $graphite = $this->mockGraphite($mockSocket);

        $device = ['hostname' => 'testhost'];
        $measurement = 'testmeasure';
        $tags = ['rrd_name' => 'rrd_name', 'ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234, 'ifOut' => 53453];

        $mockSocket->shouldReceive('write')
            ->with("testhost.testmeasure.rrd_name.ifIn 234234 $this->timestamp\n");
        $mockSocket->shouldReceive('write')
            ->with("testhost.testmeasure.rrd_name.ifOut 53453 $this->timestamp\n");
        $graphite->put($device, $measurement, $tags, $fields);
    }

    /**
     * @param mixed $mockSocket
     * @return Graphite
     */
    private function mockGraphite($mockSocket)
    {
        $mockFactory = \Mockery::mock(\Socket\Raw\Factory::class);

        $mockFactory->shouldReceive('createClient')
            ->andReturn($mockSocket);

        $graphite = new Graphite($mockFactory);

        return $graphite;
    }
}
