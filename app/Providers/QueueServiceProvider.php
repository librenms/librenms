<?php

namespace App\Providers;

use App\Jobs\Concerns\DeviceJob;
use App\Queue\QueueMetrics;
use App\Queue\QueueStatus;
use App\Queue\WorkerNode;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobReleasedAfterException;
use Illuminate\Queue\Events\Looping;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

/**
 * Queue worker status and statistics, recorded by any queue worker (Horizon, queue:work) with any queue driver
 */
class QueueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(QueueMetrics::class);
        $this->app->singleton(WorkerNode::class);
    }

    public function boot(): void
    {
        // find device jobs by device in failed jobs and retries
        Queue::createPayloadUsing(function (?string $connection, ?string $queue, array $payload): array {
            $command = $payload['data']['command'] ?? null;

            return is_object($command) && in_array(DeviceJob::class, class_uses_recursive($command))
                ? ['device_id' => $command->device_id]
                : [];
        });

        // a failure to record status is logged, it does not fail the job
        Event::listen(Looping::class, function (Looping $event): void {
            rescue(fn () => $this->app->make(WorkerNode::class)->looping((string) $event->queue));
        });
        Event::listen(JobProcessing::class, function (JobProcessing $event): void {
            $this->worker($event->job, function (Job $job): void {
                $this->app->make(QueueMetrics::class)->jobStarted($job);
                $this->app->make(WorkerNode::class)->jobStarted($job);
                $this->app->make(QueueStatus::class)->jobStarted($job);
            });
        });
        Event::listen(JobReleasedAfterException::class, function (JobReleasedAfterException $event): void {
            $this->worker($event->job, fn (Job $job) => $this->app->make(QueueStatus::class)->jobReleased($job, (int) $event->backoff));
        });
        Event::listen(JobProcessed::class, function (JobProcessed $event): void {
            $this->worker($event->job, fn (Job $job) => $this->app->make(QueueMetrics::class)->jobFinished($job, true));
        });
        Event::listen(JobExceptionOccurred::class, function (JobExceptionOccurred $event): void {
            $this->worker($event->job, fn (Job $job) => $this->app->make(QueueMetrics::class)->jobFinished($job, false));
        });
    }

    /**
     * Only jobs run by queue workers, not jobs run right away (dispatchSync, sync driver)
     *
     * @param  callable(Job): void  $callback
     */
    private function worker(Job $job, callable $callback): void
    {
        if (! $job instanceof SyncJob) {
            rescue(fn () => $callback($job));
        }
    }
}
