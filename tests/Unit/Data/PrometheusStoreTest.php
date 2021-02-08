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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Data;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LibreNMS\Config;
use LibreNMS\Data\Store\Prometheus;
use LibreNMS\Tests\TestCase;

/**
 * @group datastores
 */
class PrometheusStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('prometheus.enable', true);
        Config::set('prometheus.url', 'http://fake:9999');
    }

    public function testFailWrite()
    {
        $stack = HandlerStack::create(new MockHandler([
            new Response(422, [], 'Bad response'),
            new RequestException('Exception thrown', new Request('POST', 'test')),
        ]));

        $client = new Client(['handler' => $stack]);
        $prometheus = new Prometheus($client);

        \Log::shouldReceive('debug');
        \Log::shouldReceive('error')->once()->with("Prometheus Exception: Client error: `POST http://fake:9999/metrics/job/librenms/instance/test/measurement/none` resulted in a `422 Unprocessable Entity` response:\nBad response\n");
        \Log::shouldReceive('error')->once()->with('Prometheus Exception: Exception thrown');
        $prometheus->put(['hostname' => 'test'], 'none', [], ['one' => 1]);
        $prometheus->put(['hostname' => 'test'], 'none', [], ['one' => 1]);
    }

    public function testSimpleWrite()
    {
        $stack = HandlerStack::create(new MockHandler([
            new Response(200),
        ]));

        $container = [];
        $history = Middleware::history($container);

        $stack->push($history);
        $client = new Client(['handler' => $stack]);
        $prometheus = new Prometheus($client);

        $device = ['hostname' => 'testhost'];
        $measurement = 'testmeasure';
        $tags = ['ifName' => 'testifname', 'type' => 'testtype'];
        $fields = ['ifIn' => 234234, 'ifOut' => 53453];

        \Log::shouldReceive('debug');
        \Log::shouldReceive('error')->times(0);

        $prometheus->put($device, $measurement, $tags, $fields);

        $this->assertCount(1, $container, 'Did not receive the expected number of requests');

        /** @var Request $request */
        $request = $container[0]['request'];

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/metrics/job/librenms/instance/testhost/measurement/testmeasure/ifName/testifname/type/testtype', $request->getUri()->getPath());
        $this->assertEquals('fake', $request->getUri()->getHost());
        $this->assertEquals(9999, $request->getUri()->getPort());
        $this->assertEquals("ifIn 234234\nifOut 53453\n", (string) $request->getBody());
    }
}
