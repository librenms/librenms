<?php

namespace LibreNMS\Tests\Unit\RRD;

use LibreNMS\Exceptions\RrdCachedConnectionException;
use LibreNMS\Exceptions\RrdCorruptionException;
use LibreNMS\Exceptions\RrdDsMismatchException;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdExecutableNotFoundException;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\Exceptions\RrdPermissionException;
use LibreNMS\Exceptions\RrdUnknownException;
use LibreNMS\Exceptions\RrdUpdateTooFrequentException;
use LibreNMS\RRD\RrdProcess;
use LibreNMS\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class RrdProcessTest extends TestCase
{
    private MockInterface&LoggerInterface $logger;
    private MockInterface&Process $process;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->logger->shouldReceive('debug')->byDefault();

        $this->process = Mockery::mock(Process::class);
        $this->process->shouldReceive('setInput')->byDefault();
        $this->process->shouldReceive('setTimeout')->byDefault();
        $this->process->shouldReceive('setIdleTimeout')->byDefault();
        $this->process->shouldReceive('start')->byDefault();
        $this->process->shouldReceive('isRunning')->andReturn(false, true)->byDefault();
        $this->process->shouldReceive('clearOutput')->byDefault();
        $this->process->shouldReceive('stop')->byDefault();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testRunCommandSuccessfully(): void
    {
        $this->process->shouldReceive('getOutput')->andReturn("some output\nOK u:0.01 s:0.02 r:0.03\n");
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::OUT, "OK u:0.01 s:0.02 r:0.03\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);
        $output = $rrdProcess->run('info test.rrd');

        $this->assertEquals('some output', $output);
    }

    public function testThrowsNotFoundExceptionWhenFileIsMissing(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::OUT, "ERROR: No such file test.rrd\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);

        $this->expectException(RrdNotFoundException::class);
        $this->expectExceptionMessage('No such file test.rrd');

        $rrdProcess->run('info test.rrd');
    }

    public function testThrowsUpdateTooFrequentException(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::OUT, "ERROR: illegal attempt to update using time 123456789\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);

        $this->expectException(RrdUpdateTooFrequentException::class);
        $this->expectExceptionMessage('illegal attempt to update using time 123456789');

        $rrdProcess->run('update test.rrd N:1');
    }

    public function testThrowsCachedConnectionException(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::OUT, "ERROR: Unable to connect to rrdcached\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);

        $this->expectException(RrdCachedConnectionException::class);
        $this->expectExceptionMessage('Unable to connect to rrdcached');

        $rrdProcess->run('update test.rrd N:1');
    }

    public function testThrowsCachedConnectionExceptionWhenSocketMissing(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::OUT, "ERROR: Unable to connect to rrdcached: No such file or directory.\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);

        $this->expectException(RrdCachedConnectionException::class);
        $this->expectExceptionMessage('Unable to connect to rrdcached: No such file or directory.');

        $rrdProcess->run('update test.rrd N:1');
    }

    public function testThrowsGenericRrdException(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::OUT, "ERROR: something went wrong\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);

        $this->expectException(RrdException::class);
        $this->expectExceptionMessage('something went wrong');

        $rrdProcess->run('update test.rrd N:1');
    }

    public function testThrowsExceptionOnDatasourceCountMismatch(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::OUT, "ERROR: test.rrd: expected 2 data source readings (got 1) from N\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);

        $this->expectException(RrdDsMismatchException::class);
        $this->expectExceptionMessage('expected 2 data source readings (got 1) from N');

        $rrdProcess->run('update test.rrd N:1');
    }

    public function testThrowsExceptionOnUnknownDatasourceName(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::OUT, "ERROR: unknown DS name 'nonexistent'\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);

        $this->expectException(RrdDsMismatchException::class);
        $this->expectExceptionMessage("unknown DS name 'nonexistent'");

        $rrdProcess->run('update test.rrd -t nonexistent N:1');
    }

    public function testThrowsExceptionOnExtraDataInUpdateArgument(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::OUT, "ERROR: test.rrd: found extra data on update argument: 2:3\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);

        $this->expectException(RrdDsMismatchException::class);
        $this->expectExceptionMessage('found extra data on update argument: 2:3');

        $rrdProcess->run('update test.rrd N:1:2:3');
    }

    public function testThrowsExceptionOnExpectedTimestampNotFoundInDataSource(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(
            Process::OUT,
            "ERROR: test.rrd: expected timestamp not found in data source from foo=2\n"
        ));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);

        $this->expectException(RrdDsMismatchException::class);
        $this->expectExceptionMessage(
            'expected timestamp not found in data source from foo=2'
        );

        $rrdProcess->run('update test.rrd foo=2');
    }

    public function testThrowsExceptionOnPermissionDenied(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::OUT, "ERROR: opening 'test.rrd': Permission denied\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);

        $this->expectException(RrdPermissionException::class);
        $this->expectExceptionMessage("opening 'test.rrd': Permission denied");

        $rrdProcess->run('update test.rrd N:1');
    }

    public function testThrowsExceptionOnCorruptRrdFile(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::OUT, "ERROR: reached EOF while loading header rrd->stat_head\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);

        $this->expectException(RrdCorruptionException::class);
        $this->expectExceptionMessage('reached EOF while loading header rrd->stat_head');

        $rrdProcess->run('info test.rrd');
    }

    public function testHandlesErrorOutputOnStderr(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::ERR, "Some stderr error message\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);

        $this->expectException(RrdException::class);
        $this->expectExceptionMessage('Some stderr error message');

        $rrdProcess->run('update test.rrd N:1');
    }

    public function testThrowsExceptionWhenRrdtoolNotFound(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::ERR, "sh: line 1: exec: rrdtool: not found\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);

        $this->expectException(RrdExecutableNotFoundException::class);
        $this->expectExceptionMessage('sh: line 1: exec: rrdtool: not found');

        $rrdProcess->run('info test.rrd');
    }

    public function testThrowsUnknownExceptionForUnclassifiedErrors(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::OUT, "ERROR: something completely unexpected\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);

        $this->expectException(RrdUnknownException::class);
        $this->expectExceptionMessage('something completely unexpected');

        $rrdProcess->run('update test.rrd N:1');
    }

    public function testCanWaitForCustomString(): void
    {
        $this->process->shouldReceive('getOutput')->andReturn("some output\nDONE\n");
        $this->process->shouldReceive('waitUntil')->andReturnUsing(fn ($callback) => $callback(Process::OUT, "DONE\n"));

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);
        $output = $rrdProcess->run('info test.rrd', 'DONE');

        $this->assertEquals("some output\nDONE", $output);
    }

    public function testStartProcessOnlyIfNotRunning(): void
    {
        $process = Mockery::mock(Process::class);
        $process->shouldReceive('setInput');
        $process->shouldReceive('setTimeout');
        $process->shouldReceive('setIdleTimeout');
        $process->shouldReceive('start')->once();
        $process->shouldReceive('isRunning')->andReturn(true);
        $process->shouldReceive('stop');

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $process);
        $rrdProcess->start(); // null -> start
        $rrdProcess->start(); // isRunning(true) -> don't start

        $this->expectNotToPerformAssertions();
    }

    public function testStopProcess(): void
    {
        $this->process->shouldReceive('stop')->once();

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);
        $rrdProcess->start();
        $rrdProcess->stop();

        $this->expectNotToPerformAssertions();
    }

    public function testRunAsyncReplacesDirWithRrdcached(): void
    {
        \App\Facades\LibrenmsConfig::set('rrdcached', 'unix:/var/run/rrdcached.sock');
        \App\Facades\LibrenmsConfig::set('rrd_dir', '/opt/librenms/rrd');

        $this->process->shouldReceive('waitUntil')->andReturn(true);
        $this->process->shouldReceive('getOutput')->andReturn("OK u:0.01\n");

        $this->logger->shouldReceive('debug')
            ->with('RRD[%gupdate /test.rrd N:1%n]', ['color' => true])
            ->once();

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);
        $rrdProcess->run('update /opt/librenms/rrd/test.rrd N:1');

        $this->expectNotToPerformAssertions();
    }

    public function testDestructorStopsProcess(): void
    {
        $this->process->shouldReceive('stop')->once();

        $rrdProcess = new RrdProcess($this->logger, 300, fn () => $this->process);
        $rrdProcess->start();

        unset($rrdProcess);
        $this->expectNotToPerformAssertions();
    }
}
