<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Horizon\WorkerNode;
use Exception;
use Illuminate\Support\Str;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Laravel\Horizon\Contracts\SupervisorRepository;

/**
 * Runs Horizon as a child process, the service manager only sees this process.
 * On reload (SIGHUP) a new Horizon is started and the old one finishes its running jobs alongside it,
 * Horizon cannot do this itself because it would have to outlive its own replacement.
 */
class WorkerRun extends LnmsCommand
{
    protected $name = 'worker';

    /** seconds to wait for a new Horizon to start all of its supervisors before giving up on a reload */
    private const START_TIMEOUT = 60;
    /** seconds before starting Horizon again after it exits on its own */
    private const RESTART_DELAY = 5;

    /** @var array{process: resource, pid: int}|null the Horizon taking work */
    private ?array $current = null;
    /** @var array<int, array{process: resource, pid: int}> Horizons finishing their running jobs, by pid */
    private array $draining = [];
    private int $restartAt = 0;
    private bool $reload = false;
    private bool $stop = false;

    public function handle(MasterSupervisorRepository $masters, SupervisorRepository $supervisors): int
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGHUP, function (): void {
            $this->reload = true;
        });
        pcntl_signal(SIGTERM, function (): void {
            $this->stop = true;
        });
        pcntl_signal(SIGINT, function (): void {
            $this->stop = true;
        });

        $pid = (int) getmypid();
        file_put_contents(WorkerNode::pidFile(), $pid);

        try {
            $this->current = $this->start();

            while (! $this->stop) {
                sleep(1); // returns early on a signal

                $this->reap();

                if ($this->reload) {
                    $this->reload = false;
                    $this->handoff($masters, $supervisors);
                }

                if ($this->current === null && ! $this->stop && time() >= $this->restartAt) {
                    $this->current = $this->start();
                }
            }

            $this->shutdown();
        } finally {
            if (is_file(WorkerNode::pidFile()) && (int) file_get_contents(WorkerNode::pidFile()) === $pid) {
                unlink(WorkerNode::pidFile());
            }
        }

        return 0;
    }

    /**
     * Start a new Horizon, then stop the old one once the new one is working all of its queues
     */
    private function handoff(MasterSupervisorRepository $masters, SupervisorRepository $supervisors): void
    {
        $new = $this->start();
        $this->line("Reloading, waiting for Horizon ({$new['pid']}) to start");
        $deadline = time() + self::START_TIMEOUT;

        while (! $this->ready($new['pid'], $masters, $supervisors)) {
            if (! $this->isRunning($new)) {
                $this->error("Horizon ({$new['pid']}) exited while starting, keeping the running Horizon");

                return;
            }

            if ($this->stop || time() >= $deadline) {
                $this->error("Horizon ({$new['pid']}) did not start, keeping the running Horizon");
                $this->drain($new);

                return;
            }

            sleep(1);
            $this->reap();
        }

        if ($this->current !== null) {
            $this->line("Horizon ({$this->current['pid']}) is finishing its running jobs");
            $this->drain($this->current);
        }

        $this->current = $new;
    }

    /**
     * Horizon is running every supervisor it provisioned
     */
    private function ready(int $pid, MasterSupervisorRepository $masters, SupervisorRepository $supervisors): bool
    {
        try {
            $master = collect($masters->all())
                ->first(fn (\stdClass $master): bool => (int) $master->pid === $pid && Str::beforeLast((string) $master->name, '-') === WorkerNode::masterBasename());
            $names = (array) ($master->supervisors ?? []);

            // supervisors are listed once the master provisions them and found once each one starts
            return ! empty($names) && count($supervisors->get($names)) === count($names);
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @return array{process: resource, pid: int}
     */
    private function start(): array
    {
        $env = getenv();
        $env[WorkerNode::PARENT_ENV] = (string) getmypid();

        // output goes straight to ours, nothing has to read it
        $process = proc_open([PHP_BINARY, base_path('artisan'), 'horizon'], [0 => ['file', '/dev/null', 'r'], 1 => STDOUT, 2 => STDERR], $pipes, base_path(), $env);
        if ($process === false) {
            throw new \RuntimeException('Could not start Horizon');
        }

        $pid = (int) proc_get_status($process)['pid'];
        $this->line("Started Horizon ($pid)");

        return ['process' => $process, 'pid' => $pid];
    }

    /**
     * Horizon stops taking work and exits once its running jobs finish
     *
     * @param  array{process: resource, pid: int}  $child
     */
    private function drain(array $child): void
    {
        posix_kill($child['pid'], SIGTERM);
        $this->draining[$child['pid']] = $child;
    }

    /**
     * Forget exited Horizons, schedule a restart if the one taking work exited on its own
     */
    private function reap(): void
    {
        foreach ($this->draining as $pid => $child) {
            if (! $this->isRunning($child)) {
                $this->line("Horizon ($pid) finished");
                unset($this->draining[$pid]);
            }
        }

        if ($this->current !== null && ! $this->isRunning($this->current)) {
            $this->error("Horizon ({$this->current['pid']}) exited, starting it again in " . self::RESTART_DELAY . ' seconds');
            $this->current = null;
            $this->restartAt = time() + self::RESTART_DELAY;
        }
    }

    /**
     * Stop every Horizon and wait for their running jobs to finish
     */
    private function shutdown(): void
    {
        if ($this->current !== null) {
            $this->drain($this->current);
            $this->current = null;
        }

        $this->line('Stopping, waiting for running jobs to finish');
        while (! empty($this->draining)) {
            sleep(1);
            $this->reap();
        }
    }

    /**
     * @param  array{process: resource, pid: int}  $child
     */
    private function isRunning(array $child): bool
    {
        if (proc_get_status($child['process'])['running']) {
            return true;
        }

        proc_close($child['process']);

        return false;
    }
}
