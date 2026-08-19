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
    /** answers every command promptly, exactly like a healthy rrdtool */
    private const HEALTHY = 'while IFS= read -r line; do printf "OK u:0.01 s:0.02 r:0.03\n"; done';

    /** accepts the command and never answers, like a wedged rrdcached */
    private const UNRESPONSIVE = 'while IFS= read -r line; do sleep 30; done';

    /** answers the first command, then wedges -- rrdcached going bad mid-poll */
    private const HEALTHY_THEN_WEDGED = 'IFS= read -r line; printf "OK u:0.01 s:0.02 r:0.03\n"; '
        . 'while IFS= read -r line; do sleep 30; done';

    /**
     * emits a first line promptly, then stalls before finishing the command --
     * rrdcached accepting a command and dying partway through the reply.
     */
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

    /**
     * The reported defect.
     *
     * The poller holds one rrdtool process open for a whole poll and writes to
     * it between SNMP walks. rrdtool only speaks when spoken to, so "time since
     * last output" is really "time since the caller last asked for something".
     * On a device whose port walk takes longer than the timeout, a completely
     * healthy rrdtool is killed for the caller's slowness.
     */
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

    /**
     * The requirement the guard was added for in #18786: notice when rrdtool
     * stops answering, rather than losing writes in silence. Whatever the
     * timeout measures, it must still catch this.
     */
    public function testUnresponsiveRrdtoolIsStillKilled(): void
    {
        $rrd = $this->rrdProcess(self::UNRESPONSIVE, 1);

        $this->expectException(RrdTimeoutException::class);

        $rrd->run('update wedged.rrd N:1');
    }

    /**
     * The timeout should bound how long we wait for rrdtool to answer the
     * command we just sent -- so it runs from the send, not from the last time
     * rrdtool happened to say something.
     */
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

    /**
     * Consecutive slow gaps must not accumulate into a shorter and shorter
     * budget for rrdtool. Each command gets the whole window.
     */
    public function testSlowGapsDoNotAccumulateAcrossCommands(): void
    {
        $rrd = $this->rrdProcess(self::HEALTHY, 1);

        for ($i = 0; $i < 3; $i++) {
            usleep(700_000);
            $rrd->run("update gap$i.rrd N:$i");
        }

        $this->assertSame('', $rrd->run('update final.rrd N:1'));
    }

    /**
     * Regression guard for #18783 (71dc70f2a), which raised CheckRrdStep's
     * timeout to 120 and catches ProcessTimedOutException to report
     * "check skipped" rather than grinding through 150k files.
     *
     * That fix depends on the process lifetime remaining bounded even when
     * rrdtool answers every single command promptly, so a read-path consumer
     * asking for a lifetime must still get one. Without this test, making the
     * per-command timeout stop firing would silently reintroduce the hang
     * #18783 was written to prevent -- invisibly, since CheckRrdStep is
     * disabled unless rrd.step has been changed from its default.
     */
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

    /**
     * The pad that protects a command from the caller's think-time must not
     * outlive the start of rrdtool's reply. Otherwise a command that answers in
     * pieces and then stalls gets the preceding gap added to its budget, so a
     * long SNMP walk would buy a wedged rrdcached extra time to hang around in.
     */
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

    /**
     * An unresponsive rrdtool is a datastore fault, and must be reported as one.
     *
     * Before this change it escaped as a raw ProcessTimedOutException, which is not
     * part of the RrdException hierarchy. Rrd::write() catches RrdStoreException and
     * RrdException and neither matches, so the exception left the datastore
     * entirely and was caught by the per-module handler in PollDevice -- which logs
     * "Error polling <module> module", blaming whichever module happened to be
     * writing when the pipe died. It also never reached the three-strikes counter
     * that exists to disable a datastore that is not working.
     */
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

    /**
     * The message must say which axis fired, because they mean different things:
     * the per-command timeout means rrdtool did not answer, the lifetime means a
     * caller-imposed budget ran out.
     */
    public function testTheTimeoutMessageSaysWhatActuallyRanOut(): void
    {
        $rrd = $this->rrdProcess(self::UNRESPONSIVE, 1);

        try {
            $rrd->run('update wedged.rrd N:1');
            $this->fail('expected the unresponsive process to time out');
        } catch (RrdException $e) {
            $this->assertStringContainsString('did not respond', $e->getMessage());
        }
    }

    /**
     * The poller holds one process open for an entire poll, which legitimately
     * runs for many minutes on a high-port-count device. It must not be given a
     * lifetime bound, or a healthy rrdtool is killed for the poll being long.
     */
    public function testNoLifetimeIsAppliedUnlessOneIsAsked(): void
    {
        $rrd = $this->rrdProcess(self::HEALTHY, timeout: 1);

        // longer than the per-command timeout, spent entirely outside rrdtool
        usleep(1_500_000);
        $rrd->run('update first.rrd N:1');
        usleep(1_500_000);

        $this->assertSame('', $rrd->run('update second.rrd N:2'));
    }
}
