<?php
/**
 * PrometheusStoreTest.php
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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Data;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LibreNMS\Config;
use LibreNMS\Data\Store\Prometheus;
use LibreNMS\Tests\TestCase;
use LibreNMS\Tests\Traits\MockGuzzleClient;

/**
 * @group datastores
 */
class PrometheusStoreTest extends TestCase
{
    use MockGuzzleClient;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('prometheus.enable', true);
        Config::set('prometheus.url', 'http://fake:9999');
    }

    public function testFailWrite()
    {
        $this->mockGuzzleClient([
            new Response(422, [], 'Bad response'),
            new RequestException('Exception thrown', new Request('POST', 'test')),
        ]);

        $prometheus = app(Prometheus::class);

        \Log::shouldReceive('debug');
        \Log::shouldReceive('error')->once()->with("Prometheus Exception: Client error: `POST http://fake:9999/metrics/job/librenms/instance/test/measurement/none` resulted in a `422 Unprocessable Entity` response:\nBad response\n");
        \Log::shouldReceive('error')->once()->with('Prometheus Exception: Exception thrown');
        $prometheus->put(['hostname' => 'test'], 'none', [], ['one' => 1]);
        $prometheus->put(['hostname' => 'test'], 'none', [], ['one' => 1]);
    }

    public function testSimpleWrite()
    {
        $this->mockGuzzleClient([
            new Response(200),
        ]);

        $prometheus = app(Prometheus::class);

        $device = ['hostname' => 'testhost'];
        $measurement = 'testmeasure';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234, 'ifOut' => 53453];

        \Log::shouldReceive('debug');
        \Log::shouldReceive('error')->times(0);

        $prometheus->put($device, $measurement, $tags, $fields);

        $history = $this->guzzleRequestHistory();
        $this->assertCount(1, $history, 'Did not receive the expected number of requests');
        $this->assertEquals('POST', $history[0]->getMethod());
        $this->assertEquals('/metrics/job/librenms/instance/testhost/measurement/testmeasure/ifName/testifname/type/testtype', $history[0]->getUri()->getPath());
        $this->assertEquals('fake', $history[0]->getUri()->getHost());
        $this->assertEquals(9999, $history[0]->getUri()->getPort());
        $this->assertEquals("ifIn 234234\nifOut 53453\n", (string) $history[0]->getBody());
    }
}
