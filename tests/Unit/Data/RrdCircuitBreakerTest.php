<?php

namespace LibreNMS\Tests\Unit\Data;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Eventlog;
use Carbon\Carbon;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\Exceptions\RrdCachedConnectionException;
use LibreNMS\RRD\RrdProcess;
use LibreNMS\Tests\TestCase;
use Mockery;

final class RrdCircuitBreakerTest extends TestCase
{
    private int $runs = 0;
    private bool $failing = true;
    /** @var string[] */
    private array $events = [];

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-01-01 00:00:00');
        LibrenmsConfig::set('rrdtool_version', '1.6');
        LibrenmsConfig::set('rrd_dir', '/tmp');

        $process = Mockery::mock(RrdProcess::class);
        $process->shouldReceive('run')->andReturnUsing(function () {
            $this->runs++;
            if ($this->failing) {
                throw new RrdCachedConnectionException('connection refused');
            }

            return '';
        });
        $process->shouldReceive('stop');
        $this->app->bind(RrdProcess::class, fn () => $process);

        $eventlog = Mockery::mock(Eventlog::class);
        $eventlog->shouldReceive('_log')->andReturnUsing(function (string $text): void {
            $this->events[] = $text;
        });
        $this->app->instance(Eventlog::class, $eventlog);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function testBacksOffAndRecovers(): void
    {
        $rrd = new Rrd();

        // trips after threshold
        $this->writeTimes($rrd, 3);
        $this->assertSame(3, $this->runs);
        $this->assertCount(1, $this->events);
        $this->assertStringContainsString('suspended for 30s', $this->events[0]);

        // suspended: no rrdtool calls
        $this->writeTimes($rrd, 5);
        $this->assertSame(3, $this->runs);

        // half-open: one retry, fails, re-trips with doubled backoff
        Carbon::setTestNow(Carbon::now()->addSeconds(30));
        $this->writeTimes($rrd, 3);
        $this->assertSame(4, $this->runs);
        $this->assertStringContainsString('suspended for 60s', $this->events[1]);

        Carbon::setTestNow(Carbon::now()->addSeconds(59));
        $this->writeTimes($rrd, 1);
        $this->assertSame(4, $this->runs);

        // recovers once rrdtool works again
        $this->failing = false;
        Carbon::setTestNow(Carbon::now()->addSeconds(1));
        $this->writeTimes($rrd, 3);
        $this->assertSame(7, $this->runs);
        $this->assertSame('RRD updates resumed', $this->events[2]);

        // breaker fully reset: needs threshold failures to trip again at base backoff
        $this->failing = true;
        $this->writeTimes($rrd, 2);
        $this->assertCount(3, $this->events);
        $this->writeTimes($rrd, 1);
        $this->assertStringContainsString('suspended for 30s', $this->events[3]);
    }

    public function testBackoffIsCapped(): void
    {
        $rrd = new Rrd();
        $this->writeTimes($rrd, 3);

        for ($i = 0; $i < 10; $i++) {
            Carbon::setTestNow(Carbon::now()->addSeconds(600));
            $this->writeTimes($rrd, 1);
        }

        $this->assertStringContainsString('suspended for 600s', end($this->events));
    }

    private function writeTimes(Rrd $rrd, int $count): void
    {
        $device = new Device(['hostname' => 'test']);
        for ($i = 0; $i < $count; $i++) {
            $rrd->write('test', ['a' => 1], [], ['device' => $device]);
        }
    }
}
