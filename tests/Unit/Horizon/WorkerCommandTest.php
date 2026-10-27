<?php

namespace LibreNMS\Tests\Unit\Horizon;

use App\Horizon\WorkerNode;
use LibreNMS\Tests\TestCase;

final class WorkerCommandTest extends TestCase
{
    /** @var resource|null */
    private $process = null;
    private string $signalFile = '';

    protected function setUp(): void
    {
        parent::setUp();

        if (WorkerNode::runningPid() !== null) {
            $this->markTestSkipped('The worker command is running, its pid file would be replaced');
        }

        $this->signalFile = tempnam(sys_get_temp_dir(), 'worker-test');
        unlink($this->signalFile);
    }

    protected function tearDown(): void
    {
        if ($this->process !== null) {
            proc_terminate($this->process, SIGKILL);
            proc_close($this->process);
        }
        if ($this->signalFile !== '') { // not skipped
            @unlink(WorkerNode::pidFile());
            @unlink($this->signalFile);
        }

        parent::tearDown();
    }

    public function testNoPidFile(): void
    {
        @unlink(WorkerNode::pidFile());

        $this->assertNull(WorkerNode::runningPid());
    }

    public function testExitedWorker(): void
    {
        $pid = $this->startFakeWorker();
        proc_terminate($this->process, SIGKILL);
        proc_close($this->process);
        $this->process = null;

        file_put_contents(WorkerNode::pidFile(), $pid);

        $this->assertNull(WorkerNode::runningPid());
    }

    public function testPidReusedByAnotherProcess(): void
    {
        file_put_contents(WorkerNode::pidFile(), getmypid());

        $this->assertNull(WorkerNode::runningPid());
    }

    public function testRunningWorker(): void
    {
        $pid = $this->startFakeWorker();
        file_put_contents(WorkerNode::pidFile(), $pid);

        $this->assertSame($pid, WorkerNode::runningPid());
    }

    public function testReloadCommandSignalsTheWorker(): void
    {
        $pid = $this->startFakeWorker();
        file_put_contents(WorkerNode::pidFile(), $pid);

        $this->artisan('worker:reload')->assertExitCode(0);

        for ($i = 0; $i < 50 && ! is_file($this->signalFile); $i++) {
            usleep(100_000);
        }
        $this->assertFileExists($this->signalFile);
    }

    public function testHorizonNotStartedByTheWorkerIsNotReloaded(): void
    {
        putenv(WorkerNode::PARENT_ENV);
        $this->assertFalse(WorkerNode::requestReload());

        // only the direct parent is signaled
        putenv(WorkerNode::PARENT_ENV . '=' . getmypid());
        $this->assertFalse(WorkerNode::requestReload());
        putenv(WorkerNode::PARENT_ENV);
    }

    /**
     * A process with "worker" in its command line that writes the signal file on SIGHUP
     */
    private function startFakeWorker(): int
    {
        $code = sprintf('pcntl_async_signals(true); pcntl_signal(SIGHUP, fn () => touch(%s)); file_put_contents("php://stdout", "ready\n"); sleep(10);', var_export($this->signalFile, true));
        $this->process = proc_open([PHP_BINARY, '-r', $code, 'worker'], [1 => ['pipe', 'w']], $pipes);
        $this->assertIsResource($this->process);
        fgets($pipes[1]); // the signal handler is installed

        return (int) proc_get_status($this->process)['pid'];
    }
}
