<?php

/**
 * RrdProcessTimeoutTest.php
 *
 * Process lifecycle tests for RrdProcess.
 *
 * These drive a real Symfony Process against a shell script standing in for
 * rrdtool, because the behaviour under test is Symfony's own timeout
 * bookkeeping. A mocked Process cannot demonstrate it.
 *
 * No rrdtool binary and no database are required.
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

namespace LibreNMS\Tests\Unit\RRD;

use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdStoreException;
use LibreNMS\Exceptions\RrdTimeoutException;
use LibreNMS\RRD\RrdProcess;
use LibreNMS\Tests\TestCase;
use Mockery;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class RrdProcessTimeoutTest extends TestCase
{
    private const HEALTHY = 'while IFS= read -r line; do printf "OK u:0.01 s:0.02 r:0.03\n"; done';

    private const UNRESPONSIVE = 'while IFS= read -r line; do sleep 30; done';

    private const HEALTHY_THEN_WEDGED = 'IFS= read -r line; printf "OK u:0.01 s:0.02 r:0.03\n"; '
        . 'while IFS= read -r line; do sleep 30; done';

    private const ANSWERS_THEN_STALLS = 'IFS= read -r line; printf "OK u:0.01 s:0.02 r:0.03\n"; '
        . 'while IFS= read -r line; do printf "partial\n"; sleep 30; done';

    private LoggerInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->logger->shouldReceive('debug')->byDefault();
        $this->logger->shouldReceive('warning')->byDefault();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function rrdProcess(string $script, int $timeout, ?int $lifetime = null): RrdProcess
    {
        return new RrdProcess($this->logger, $timeout, fn () => new Process(['sh', '-c', $script]), $lifetime);
    }

    public function testHealthyRrdtoolSurvivesACallerThatIsSlowBetweenCommands(): void
    {
        $rrd = $this->rrdProcess(self::HEALTHY, 1);

        $rrd->run('update first.rrd N:1');

        // stand in for a slow SNMP walk: we ask rrdtool for nothing at all
        usleep(1_500_000);

        $rrd->run('update second.rrd N:2');

        // reaching here at all is the assertion; the process must still be usable
        $this->assertSame('', $rrd->run('update third.rrd N:3'));
    }

    public function testTheTimeoutRunsFromWhenTheCommandWasSent(): void
    {
        $rrd = $this->rrdProcess(self::HEALTHY_THEN_WEDGED, 2);

        // first command answers, so the process is now established and running
        $rrd->run('update fine.rrd N:1');

        // burn most of the window without asking rrdtool for anything
        usleep(1_500_000);

        $start = microtime(true);

        try {
            $rrd->run('update wedged.rrd N:2');
            $this->fail('expected the unresponsive process to time out');
        } catch (RrdTimeoutException) {
            $elapsed = microtime(true) - $start;
        }

        // measured from the send this is ~2s; from the last output it is ~0.5s
        $this->assertGreaterThan(1.5, $elapsed, 'the timeout did not run from the send');
    }

    public function testAnExplicitLifetimeStillBoundsAStreamOfFastCommands(): void
    {
        $rrd = $this->rrdProcess(self::HEALTHY, timeout: 30, lifetime: 1);

        $caught = null;
        $completed = 0;
        $giveUp = microtime(true) + 5;

        try {
            // a healthy rrdtool answering as fast as it can, for longer than the lifetime.
            // bounded by wall clock so that a lifetime which never fires fails the test
            // rather than looping until phpunit is killed.
            while (microtime(true) < $giveUp) {
                $rrd->run("info file$completed.rrd");
                $completed++;
            }
        } catch (RrdTimeoutException $e) {
            $caught = $e;
        }

        $this->assertNotNull($caught, 'an explicit lifetime must still be enforced');
        $previous = $caught->getPrevious();
        $this->assertInstanceOf(ProcessTimedOutException::class, $previous, 'the Symfony exception should be kept as the cause');
        $this->assertTrue($previous->isGeneralTimeout(), 'the lifetime axis should be what fires here');
        $this->assertGreaterThan(0, $completed, 'commands should succeed until the lifetime is reached');
    }

    public function testTheWideningDoesNotOutliveTheStartOfTheReply(): void
    {
        $rrd = $this->rrdProcess(self::ANSWERS_THEN_STALLS, 1);

        $rrd->run('update fine.rrd N:1');

        // a long gap doing no rrd work at all, as the poller does while walking
        usleep(2_000_000);

        $start = microtime(true);

        try {
            $rrd->run('update stalls.rrd N:2');
            $this->fail('expected the stalled command to time out');
        } catch (RrdTimeoutException) {
            $elapsed = microtime(true) - $start;
        }

        // ~1s: the deadline runs from the send, then is renewed when "partial" arrives.
        // Without that renewal the 2s pad is still in place, giving ~3s.
        $this->assertLessThan(2.5, $elapsed, 'the caller gap was still inflating the window after rrdtool replied');
    }

    public function testAnUnresponsiveRrdtoolIsReportedAsADatastoreFault(): void
    {
        $rrd = $this->rrdProcess(self::UNRESPONSIVE, 1);

        try {
            $rrd->run('update wedged.rrd N:1');
            $this->fail('expected the unresponsive process to time out');
        } catch (RrdException $e) {
            $this->assertInstanceOf(RrdStoreException::class, $e, 'a timeout must reach the three-strikes counter');
            $this->assertInstanceOf(ProcessTimedOutException::class, $e->getPrevious(), 'the Symfony exception should be kept as the cause');
        }
    }
}
