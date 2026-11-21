<?php

namespace LibreNMS\Tests\Unit\Data;

use App\Data\TimeSeries\MetricIdentity;
use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use InfluxDB\Point;
use LibreNMS\Data\Store\Datastore;
use LibreNMS\Data\Store\Graphite;
use LibreNMS\Data\Store\InfluxDB;
use LibreNMS\Data\Store\Prometheus;
use LibreNMS\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

/**
 * What datastores other than rrd write for identified metrics, through the datastore like the poller.
 * Metrics stored under the names they had are written exactly as before they had an identity.
 */
#[Group('datastores')]
final class MetricStoresTest extends TestCase
{
    private const int TIMESTAMP = 1197464400;

    private \InfluxDB\Database&Mockery\MockInterface $influx;
    private \Socket\Raw\Socket&Mockery\MockInterface $socket;
    /** @var list<Point> */
    private array $points = [];

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::createFromTimestampUTC(self::TIMESTAMP));
        LibrenmsConfig::set('graphite.enable', true);
        LibrenmsConfig::set('prometheus.enable', true);
        LibrenmsConfig::set('prometheus.url', 'http://fake:9999');
        Http::fake(['*' => Http::response()]);

        $device = new Device(['hostname' => 'testhost']);
        $device->device_id = 42;
        DeviceCache::fake($device);
        DeviceCache::setPrimary($device->device_id);

        $this->influx = Mockery::mock(\InfluxDB\Database::class);
        $this->influx->shouldReceive('writePoints')->andReturnUsing(function (array $points): bool {
            array_push($this->points, ...$points);

            return true;
        });
        $this->socket = Mockery::mock(\Socket\Raw\Socket::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function testLegacyProcessorsAreWrittenAsBefore(): void
    {
        $this->expectGraphite(['testhost.processors.processor.hr.1.usage 5']);

        $this->write($this->processor(), ['usage' => 5], $this->processor()->tags);

        $this->assertPoint('processors', ['hostname' => 'testhost', 'processor_type' => 'hr', 'processor_index' => '1'], ['usage' => 5]);
        $this->assertPrometheus('testhost/measurement/processors/processor_type/hr/processor_index/1');
    }

    public function testLegacyPortsAreWrittenAsBefore(): void
    {
        $this->expectGraphite(['testhost.ports.eth0.INOCTETS 100', 'testhost.ports.eth0.OUTOCTETS 200', 'testhost.ports.eth0.ifInOctets_rate 3']);

        $portTags = ['ifName' => 'eth0', 'ifAlias' => 'uplink', 'ifIndex' => 2, 'port_descr_type' => 'core'];
        $this->write($this->port(), ['in_octets' => 100, 'out_octets' => 200, 'ifInOctets_rate' => 3], [...$this->port()->tags, ...$portTags]);

        $this->assertPoint('ports', ['hostname' => 'testhost', ...$portTags], ['INOCTETS' => 100, 'OUTOCTETS' => 200, 'ifInOctets_rate' => 3]);
        $this->assertPrometheus('testhost/measurement/ports/ifName/eth0/ifAlias/uplink/ifIndex/2/port_descr_type/core');
    }

    public function testCanonicalPortsAreWrittenAsIdentified(): void
    {
        LibrenmsConfig::set('datastore.canonical_metrics', true);
        $this->expectGraphite(['testhost.port.12.in_octets 100']);

        $this->write($this->port(), ['in_octets' => 100], $this->port()->tags);

        $this->assertPoint('port', ['hostname' => 'testhost', 'id' => 12], ['in_octets' => 100]);
        $this->assertPrometheus('testhost/measurement/port/hostname/testhost/id/12');
    }

    public function testCanonicalProcessorsAreWrittenAsIdentified(): void
    {
        LibrenmsConfig::set('datastore.canonical_metrics', true);
        $this->expectGraphite(['testhost.processor.hr.1.usage 5']);

        $this->write($this->processor(), ['usage' => 5], $this->processor()->tags);

        $this->assertPoint('processor', ['hostname' => 'testhost', 'type' => 'hr', 'index' => '1'], ['usage' => 5]);
        $this->assertPrometheus('testhost/measurement/processor/hostname/testhost/type/hr/index/1');
    }

    private function processor(): MetricIdentity
    {
        return new MetricIdentity('processor', ['hostname' => 'testhost', 'type' => 'hr', 'index' => '1']);
    }

    private function port(): MetricIdentity
    {
        return new MetricIdentity('port', ['hostname' => 'testhost', 'id' => 12]);
    }

    /**
     * @param  array<string, int|float>  $fields
     * @param  array<string, scalar>  $tags
     */
    private function write(MetricIdentity $metric, array $fields, array $tags): void
    {
        $socketFactory = Mockery::mock(\Socket\Raw\Factory::class);
        $socketFactory->shouldReceive('createClient')->andReturn($this->socket);

        $stores = [new InfluxDB($this->influx), new Graphite($socketFactory), app(Prometheus::class)];
        new Datastore($stores)->write($metric->measurement, $fields, $tags, ['metric' => $metric]);
    }

    /**
     * @param  list<string>  $lines  without the timestamp
     */
    private function expectGraphite(array $lines): void
    {
        foreach ($lines as $line) {
            $this->socket->shouldReceive('write')->with("$line " . self::TIMESTAMP . "\n")->once();
        }
    }

    /**
     * @param  array<string, scalar>  $tags  in order
     * @param  array<string, int|float>  $fields
     */
    private function assertPoint(string $measurement, array $tags, array $fields): void
    {
        $this->assertCount(1, $this->points);
        $this->assertSame($measurement, $this->points[0]->getMeasurement());
        $this->assertSame(array_keys($tags), array_keys($this->points[0]->getTags()));
        $this->assertEquals($tags, $this->points[0]->getTags());
        $this->assertEquals($fields, $this->points[0]->getFields());
    }

    private function assertPrometheus(string $path): void
    {
        Http::assertSent(fn (Request $request) => $request->url() === "http://fake:9999/metrics/job/librenms/instance/$path");
    }
}
