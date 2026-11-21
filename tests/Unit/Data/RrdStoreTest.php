<?php

namespace LibreNMS\Tests\Unit\Data;

use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Eventlog;
use LibreNMS\Data\Store\MetricDefinitions;
use LibreNMS\Data\Store\MetricNames;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\Exceptions\RrdCachedConnectionException;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Tests\TestCase;
use Mockery;

final class RrdStoreTest extends TestCase
{
    private Mockery\MockInterface $backend;
    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backend = Mockery::mock(RrdBackendInterface::class);
        $this->app->instance(RrdBackendInterface::class, $this->backend);
        $this->device = new Device(['hostname' => 'rrd-store-test']);
    }

    public function testCreatesMissingFile(): void
    {
        LibrenmsConfig::set('rrd.step', 300);
        $definition = RrdDefinition::make()->addDataset('a', 'GAUGE');
        $this->backend->shouldReceive('update')->once()->andThrow(new RrdNotFoundException('No such file'));
        $this->backend->shouldReceive('create')->once()->withArgs(fn ($rrd, $def) => $rrd->relativePath() === 'rrd-store-test/test.rrd' && $def->getStep() === 60);
        $this->backend->shouldReceive('update')->once()->with(Mockery::any(), [5], null);

        (new Rrd)->write('test', ['a' => 5, 'unused' => 1], [], ['device' => $this->device, 'rrd_def' => $definition, 'rrd_step' => 60]);

        $this->assertSame(300, $definition->getStep(), 'rrd_step must not change the caller\'s definition');
    }

    public function testValuesFollowDefinitionOrder(): void
    {
        $definition = RrdDefinition::make()->addDataset('in', 'COUNTER')->addDataset('out', 'COUNTER')->addDataset('errors', 'COUNTER');
        $this->backend->shouldReceive('update')->once()->with(Mockery::any(), [10, 20, null], null);

        (new Rrd)->write('test', ['out' => 20, 'in' => 10], [], ['device' => $this->device, 'rrd_def' => $definition]);
    }

    public function testAMetricIsCreatedFromItsDefinitionUnderTheNamesItHad(): void
    {
        $this->withRrdDir(function (): void {
            $metric = new MetricIdentity('port', ['hostname' => 'rrd-store-test', 'id' => 12]);
            $expected = RrdDefinition::fromMetric(new MetricDefinitions()->of('port'), new MetricNames()->legacyOf($metric));
            $this->backend->shouldReceive('update')->once()->andThrow(new RrdNotFoundException('No such file'));
            $this->backend->shouldReceive('create')->once()->withArgs(fn ($rrd, RrdDefinition $def) => $rrd->relativePath() === 'rrd-store-test/port-id12.rrd'
                && $def->getCreateArguments() === $expected->getCreateArguments()
                && str_starts_with($def->getArguments()[0], 'DS:INOCTETS:DERIVE:'));
            $this->backend->shouldReceive('update')->once()->with(Mockery::any(), [1, 2, ...array_fill(0, 13, null)], null);

            // the datastore passes the fields under the names they are stored under
            (new Rrd)->write('ports', ['OUTOCTETS' => 2, 'INOCTETS' => 1], [], ['metric' => $metric]);
        });
    }

    public function testACanonicalMetricIsCreatedFromItsDefinition(): void
    {
        LibrenmsConfig::set('datastore.canonical_metrics', true);
        $this->withRrdDir(function (): void {
            $metric = new MetricIdentity('port', ['hostname' => 'rrd-store-test', 'id' => 12]);
            $this->backend->shouldReceive('update')->once()->andThrow(new RrdNotFoundException('No such file'));
            $this->backend->shouldReceive('create')->once()->withArgs(fn ($rrd, RrdDefinition $def) => $rrd->relativePath() === 'rrd-store-test/port-12.rrd'
                && str_starts_with($def->getArguments()[0], 'DS:in_octets:DERIVE:'));
            $this->backend->shouldReceive('update')->once()->with(Mockery::any(), [1, 2, ...array_fill(0, 13, null)], null);

            (new Rrd)->write('port', ['out_octets' => 2, 'in_octets' => 1], [], ['metric' => $metric]);
        });
    }

    public function testAGivenRrdDefinitionOverridesTheMetricDefinition(): void
    {
        $this->withRrdDir(function (): void {
            $metric = new MetricIdentity('processor', ['hostname' => 'rrd-store-test', 'type' => 'hr', 'index' => 1]);
            $definition = RrdDefinition::make()->addDataset('other', 'GAUGE')->addDataset('usage', 'GAUGE');
            $this->backend->shouldReceive('update')->once()->with(Mockery::any(), [20, 10], null);

            (new Rrd)->write('processors', ['usage' => 10, 'other' => 20], [], ['metric' => $metric, 'rrd_def' => $definition]);
        });
    }

    /**
     * Metrics make sure their directory exists, keep it out of the real rrd directory
     */
    private function withRrdDir(callable $test): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('rrd-store-test-');
        mkdir($dir . DIRECTORY_SEPARATOR . 'rrd-store-test', 0775, true);
        LibrenmsConfig::set('rrd_dir', $dir);

        try {
            $test();
        } finally {
            rmdir($dir . DIRECTORY_SEPARATOR . 'rrd-store-test');
            rmdir($dir);
        }
    }

    public function testOnlyConsecutiveStoreErrorsDisable(): void
    {
        $eventlog = Mockery::mock(Eventlog::class);
        $eventlog->shouldReceive('_log')->once();
        $this->app->instance(Eventlog::class, $eventlog);

        $error = new RrdCachedConnectionException('Unable to connect to rrdcached');
        $results = [$error, $error, null, $error, $error, $error]; // null is a successful update
        $this->backend->shouldReceive('update')->times(6)->andReturnUsing(function () use (&$results): void {
            if ($error = array_shift($results)) {
                throw $error;
            }
        });

        $rrd = new Rrd;
        foreach (range(1, 7) as $ignored) {
            $rrd->write('test', ['a' => 1], [], ['device' => $this->device]); // the 7th write is skipped
        }
    }
}
