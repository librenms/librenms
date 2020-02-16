<?php
/**
 * OpenTSDBStoreTest.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Data;

use Carbon\Carbon;
use GuzzleHttp\Client;
use LibreNMS\Data\Store\OpenTSDB;
use LibreNMS\Tests\TestCase;

/**
 * @group datastores
 */
class OpenTSDBStoreTest extends TestCase
{
    protected $timestamp = 990464400;

    public function setUp() : void
    {
        parent::setUp();

        // fix the date
        Carbon::setTestNow(Carbon::createFromTimestamp($this->timestamp));
    }

    public function tearDown() : void
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

        new OpenTSDB($mockFactory);
    }

    public function testSocketWriteError()
    {
        $mockSocket = \Mockery::mock(\Socket\Raw\Socket::class);
        $opentsdb = $this->mockOpenTSDB($mockSocket);

        $mockSocket->shouldReceive('write')
            ->andThrow('Socket\Raw\Exception', 'Did not handle socket exception');

        $opentsdb->put(['hostname' => 'test'], 'fake', [], ['one' => 1]);
    }

    public function testSimpleWrite()
    {
        $mockSocket = \Mockery::mock(\Socket\Raw\Socket::class);
        $opentsdb = $this->mockOpenTSDB($mockSocket);

        $device = ['hostname' => 'testhost'];
        $measurement = 'testmeasure';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234, 'ifOut' => 53453];

        $mockSocket->shouldReceive('write')
            ->with("put net.testmeasure $this->timestamp 0.000000 234234\n");
        $mockSocket->shouldReceive('write')
            ->with("put net.testmeasure $this->timestamp 0.000000 53453\n");
        $opentsdb->put($device, $measurement, $tags, $fields);
    }

    public function testPortWrite()
    {
        $mockSocket = \Mockery::mock(\Socket\Raw\Socket::class);
        $opentsdb = $this->mockOpenTSDB($mockSocket);

        $device = ['hostname' => 'testhost'];
        $measurement = 'port';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 897238, 'ifOut' => 2342];

        $mockSocket->shouldReceive('write')
            ->with("put net.port.ifin $this->timestamp 0.000000 897238\n");
        $mockSocket->shouldReceive('write')
            ->with("put net.port.ifout $this->timestamp 0.000000 2342\n");
        $opentsdb->put($device, $measurement, $tags, $fields);
    }

    /**
     * @param $mockSocket
     * @return OpenTSDB
     */
    private function mockOpenTSDB($mockSocket)
    {
        $mockFactory = \Mockery::mock(\Socket\Raw\Factory::class);

        $mockFactory->shouldReceive('createClient')
            ->andReturn($mockSocket);

        return new OpenTSDB($mockFactory);
    }
}
