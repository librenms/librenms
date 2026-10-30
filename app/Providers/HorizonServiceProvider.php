<?php

namespace App\Providers;

use App\Horizon\DatastoreMetricsRepository;
use App\Horizon\QueueStatus;
use App\Horizon\WorkerNode;
use Illuminate\Console\Application;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobReleasedAfterException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Console\HorizonCommand;
use Laravel\Horizon\Contracts\MetricsRepository;
use Laravel\Horizon\Events\MasterSupervisorLooped;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;
use Laravel\Horizon\MasterSupervisor;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MetricsRepository::class, DatastoreMetricsRepository::class);
        $this->app->singleton(WorkerNode::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // lnms worker runs horizon, service files use it so what runs can change without editing them
        Application::starting(fn (Application $artisan) => $artisan->add($this->app->make(HorizonCommand::class)->setAliases(['worker'])));

        // name Horizon after the node so it matches the node's settings
        MasterSupervisor::$nameResolver = fn (): string => WorkerNode::masterBasename();

        // only the master reads the supervisor config, LibreNMS config includes database settings once the app has booted
        Event::listen(CommandStarting::class, function (CommandStarting $event): void {
            if ($event->command == 'horizon') {
                $this->app->make(WorkerNode::class)->start();
            }
        });
        Event::listen(MasterSupervisorLooped::class, fn (MasterSupervisorLooped $event) => $this->app->make(WorkerNode::class)->looped($event->master));
        Event::listen(JobProcessing::class, function (JobProcessing $event): void {
            QueueStatus::jobStarted($event->job);
            $this->app->make(WorkerNode::class)->jobStarted($event->job);
        });
        Event::listen(JobReleasedAfterException::class, fn (JobReleasedAfterException $event) => QueueStatus::jobReleased($event->job, (int) $event->backoff));
        Event::listen(JobProcessed::class, fn (JobProcessed $event) => $this->app->make(WorkerNode::class)->jobFinished($event->job, true));
        Event::listen(JobExceptionOccurred::class, fn (JobExceptionOccurred $event) => $this->app->make(WorkerNode::class)->jobFinished($event->job, false));
    }

    /**
     * Admins only, without Horizon's open access in the local environment
     */
    protected function authorization(): void
    {
        Horizon::auth(fn ($request): bool => QueueStatus::available() && Gate::check('worker.horizon', [$request->user()]));
    }
}
