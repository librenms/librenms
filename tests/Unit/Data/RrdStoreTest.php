<?php

namespace LibreNMS\Tests\Unit\Data;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Eventlog;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\Exceptions\RrdCachedConnectionException;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\RRD\RrdPath;
use LibreNMS\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Exception\RuntimeException as ProcessRuntimeException;

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

    public function testCreatesMissingRemoteFileAfterRrdcachedStatErrorZero(): void
    {
        $definition = RrdDefinition::make()->addDataset('a', 'GAUGE');
        $rrd = Mockery::on(fn (RrdPath $path) => $path->relativePath() === 'rrd-store-test/test.rrd');
        $this->backend->shouldReceive('update')->once()->with($rrd, [5], null)->ordered()
            ->andThrow(RrdException::parse('ERROR: rrdcached@rrdcached:42217: stat failed with error 0.'));
        $this->backend->shouldReceive('remote')->andReturnTrue();
        $this->backend->shouldReceive('last')->once()->with($rrd)->ordered()
            ->andThrow(new RrdNotFoundException('No such file'));
        $this->backend->shouldReceive('create')->once()->with($rrd, $definition)->ordered();
        $this->backend->shouldReceive('update')->once()->with($rrd, [5], null)->ordered();

        (new Rrd)->write('test', ['a' => 5], [], ['device' => $this->device, 'rrd_def' => $definition]);
    }

    public function testKeepsExistingRemoteFileAfterRrdcachedStatErrorZero(): void
    {
        $definition = RrdDefinition::make()->addDataset('a', 'GAUGE');
        $rrd = Mockery::on(fn (RrdPath $path) => $path->relativePath() === 'rrd-store-test/test.rrd');
        $this->backend->shouldReceive('update')->once()->with($rrd, [5], null)
            ->andThrow(RrdException::parse('ERROR: rrdcached@rrdcached:42217: stat failed with error 0'));
        $this->backend->shouldReceive('remote')->andReturnTrue();
        $this->backend->shouldReceive('last')->once()->with($rrd)->andReturn(1798761600);
        $this->backend->shouldReceive('create')->never();

        (new Rrd)->write('test', ['a' => 5], [], ['device' => $this->device, 'rrd_def' => $definition]);
    }

    /** @return array<string, array{bool}> */
    public static function remoteProbeStages(): array
    {
        return [
            'rename check' => [true],
            'failed update recovery' => [false],
        ];
    }

    #[DataProvider('remoteProbeStages')]
    public function testFailedRemoteProbeAbortsWriteWithoutCreatingFile(bool $checkRename): void
    {
        $meta = ['device' => $this->device, 'rrd_def' => RrdDefinition::make()->addDataset('a', 'GAUGE')];
        if ($checkRename) {
            LibrenmsConfig::set('rrdcached', 'rrdcached:42217');
            LibrenmsConfig::set('rrd_dir', sys_get_temp_dir() . '/librenms-rrd-test-' . uniqid());
            $meta['rrd_oldname'] = 'legacy';
            $this->backend->shouldReceive('update')->never();
        } else {
            $this->backend->shouldReceive('update')->once()
                ->andThrow(RrdException::parse('rrdcached@rrdcached:42217: stat failed with error 0'));
        }
        $this->backend->shouldReceive('remote')->andReturnTrue();
        $this->backend->shouldReceive('last')->once()->andThrow(new ProcessRuntimeException('Process failed to start'));
        $this->backend->shouldReceive('create')->never();

        (new Rrd)->write('test', ['a' => 5], [], $meta);
    }

    #[DataProvider('remoteProbeStages')]
    public function testRrdcachedTransportErrorsSuspendWrites(bool $checkRename): void
    {
        $eventlog = Mockery::mock(Eventlog::class);
        $eventlog->shouldReceive('_log')->once();
        $this->app->instance(Eventlog::class, $eventlog);

        $error = RrdException::parse('ERROR: request: socket error (32) while talking to rrdcached');
        $meta = ['device' => $this->device];
        if ($checkRename) {
            LibrenmsConfig::set('rrdcached', 'rrdcached:42217');
            LibrenmsConfig::set('rrd_dir', sys_get_temp_dir() . '/librenms-rrd-test-' . uniqid());
            $meta['rrd_oldname'] = 'legacy';
            $this->backend->shouldReceive('remote')->andReturnTrue();
            $this->backend->shouldReceive('last')->times(3)->andThrow($error);
            $this->backend->shouldReceive('update')->never();
        } else {
            $this->backend->shouldReceive('update')->times(3)->andThrow($error);
        }

        $rrd = new Rrd;
        foreach (range(1, 4) as $ignored) {
            $rrd->write('test', ['a' => 1], [], $meta); // the 4th write is skipped
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
