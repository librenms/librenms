<?php

namespace LibreNMS\Tests\Unit\RRD;

use LibreNMS\Exceptions\RrdCachedConnectionException;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\Exceptions\RrdUpdateTooFrequentException;
use LibreNMS\RRD\RrdProcess;
use LibreNMS\Tests\TestCase;
use Mockery;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class RrdProcessTest extends TestCase
{
    private $logger;
    private $process;

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
        $this->process->shouldReceive('waitUntil')->andReturnUsing(function ($callback) {
            return $callback(Process::OUT, "OK u:0.01 s:0.02 r:0.03\n");
        });

        $rrdProcess = new RrdProcess($this->logger, 300, fn() => $this->process);
        $output = $rrdProcess->run('info test.rrd');

        $this->assertEquals("some output", $output);
    }

    public function testThrowsNotFoundExceptionWhenFileIsMissing(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(function ($callback) {
            return $callback(Process::OUT, "ERROR: No such file test.rrd\n");
        });

        $rrdProcess = new RrdProcess($this->logger, 300, fn() => $this->process);

        $this->expectException(RrdNotFoundException::class);
        $this->expectExceptionMessage('No such file test.rrd');

        $rrdProcess->run('info test.rrd');
    }

    public function testThrowsUpdateTooFrequentException(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(function ($callback) {
            return $callback(Process::OUT, "ERROR: illegal attempt to update using time 123456789\n");
        });

        $rrdProcess = new RrdProcess($this->logger, 300, fn() => $this->process);

        $this->expectException(RrdUpdateTooFrequentException::class);
        $this->expectExceptionMessage('illegal attempt to update using time 123456789');

        $rrdProcess->run('update test.rrd N:1');
    }

    public function testThrowsCachedConnectionException(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(function ($callback) {
            return $callback(Process::OUT, "ERROR: Unable to connect to rrdcached\n");
        });

        $rrdProcess = new RrdProcess($this->logger, 300, fn() => $this->process);

        $this->expectException(RrdCachedConnectionException::class);
        $this->expectExceptionMessage('Unable to connect to rrdcached');

        $rrdProcess->run('update test.rrd N:1');
    }

    public function testThrowsGenericRrdException(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(function ($callback) {
            return $callback(Process::OUT, "ERROR: something went wrong\n");
        });

        $rrdProcess = new RrdProcess($this->logger, 300, fn() => $this->process);

        $this->expectException(RrdException::class);
        $this->expectExceptionMessage('something went wrong');

        $rrdProcess->run('update test.rrd N:1');
    }

    public function testHandlesErrorOutputOnStderr(): void
    {
        $this->process->shouldReceive('waitUntil')->andReturnUsing(function ($callback) {
            return $callback(Process::ERR, "Some stderr error message\n");
        });

        $rrdProcess = new RrdProcess($this->logger, 300, fn() => $this->process);

        $this->expectException(RrdException::class);
        $this->expectExceptionMessage('Some stderr error message');

        $rrdProcess->run('update test.rrd N:1');
    }

    public function testCanWaitForCustomString(): void
    {
        $this->process->shouldReceive('getOutput')->andReturn("some output\nDONE\n");
        $this->process->shouldReceive('waitUntil')->andReturnUsing(function ($callback) {
            return $callback(Process::OUT, "DONE\n");
        });

        $rrdProcess = new RrdProcess($this->logger, 300, fn() => $this->process);
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

        $rrdProcess = new RrdProcess($this->logger, 300, fn() => $process);
        $rrdProcess->start(); // null -> start
        $rrdProcess->start(); // isRunning(true) -> don't start

        $this->assertTrue(true);
    }

    public function testStopProcess(): void
    {
        $this->process->shouldReceive('stop')->once();

        $rrdProcess = new RrdProcess($this->logger, 300, fn() => $this->process);
        $rrdProcess->start();
        $rrdProcess->stop();

        $this->assertTrue(true);
    }

    public function testRunAsyncReplacesDirWithRrdcached(): void
    {
        \App\Facades\LibrenmsConfig::set('rrdcached', 'unix:/var/run/rrdcached.sock');
        \App\Facades\LibrenmsConfig::set('rrd_dir', '/opt/librenms/rrd');

        $this->process->shouldReceive('waitUntil')->andReturn(true);
        $this->process->shouldReceive('getOutput')->andReturn("OK u:0.01\n");

        $this->logger->shouldReceive('debug')
            ->with("RRD[%gupdate /test.rrd N:1%n]", ['color' => true])
            ->once();

        $rrdProcess = new RrdProcess($this->logger, 300, fn() => $this->process);
        $rrdProcess->run('update /opt/librenms/rrd/test.rrd N:1');

        $this->assertTrue(true);
    }

    public function testDestructorStopsProcess(): void
    {
        $this->process->shouldReceive('stop')->once();

        $rrdProcess = new RrdProcess($this->logger, 300, fn() => $this->process);
        $rrdProcess->start();

        // Trigger destructor
        unset($rrdProcess);
        $this->assertTrue(true);
    }
}
