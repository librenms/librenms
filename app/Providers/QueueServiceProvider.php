<?php

namespace App\Providers;

use App\Queue\JobPayload;
use App\Queue\RetryTracker;
use App\Queue\WorkedQueues;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobReleasedAfterException;
use Illuminate\Queue\Events\Looping;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

/**
 * Queue status recorded by any queue worker (Horizon, queue:work) with any queue driver
 */
class QueueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // state of this worker process
        $this->app->singleton(WorkedQueues::class);
    }

    public function boot(): void
    {
        Queue::createPayloadUsing(fn (?string $connection, ?string $queue, array $payload): array => JobPayload::extra($payload));

        // a failure to record status is logged, it does not fail the job
        Event::listen(Looping::class, function (Looping $event): void {
            rescue(fn () => $this->app->make(WorkedQueues::class)->looping((string) $event->queue));
        });
        Event::listen(JobProcessing::class, function (JobProcessing $event): void {
            $this->worker($event->job, function (Job $job): void {
                $this->app->make(WorkedQueues::class)->jobStarted($job);
                $this->app->make(RetryTracker::class)->jobStarted($job);
            });
        });
        Event::listen(JobReleasedAfterException::class, function (JobReleasedAfterException $event): void {
            $this->worker($event->job, fn (Job $job) => $this->app->make(RetryTracker::class)->jobReleased($job, (int) $event->backoff));
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
