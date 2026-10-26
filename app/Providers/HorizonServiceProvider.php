<?php

namespace App\Providers;

use App\Horizon\DatastoreMetricsRepository;
use App\Horizon\HorizonNode;
use App\Horizon\QueueStatus;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Contracts\MetricsRepository;
use Laravel\Horizon\Events\MasterSupervisorLooped;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MetricsRepository::class, DatastoreMetricsRepository::class);
        $this->app->singleton(HorizonNode::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // only the master reads the supervisor config, LibreNMS config includes database settings once the app has booted
        Event::listen(CommandStarting::class, function (CommandStarting $event): void {
            if ($event->command == 'horizon') {
                $this->app->make(HorizonNode::class)->start();
            }
        });
        Event::listen(MasterSupervisorLooped::class, fn (MasterSupervisorLooped $event) => $this->app->make(HorizonNode::class)->looped($event->master));
        Event::listen(JobProcessing::class, fn (JobProcessing $event) => $this->app->make(HorizonNode::class)->jobStarted($event->job));
        Event::listen(JobProcessed::class, fn (JobProcessed $event) => $this->app->make(HorizonNode::class)->jobFinished($event->job, true));
        Event::listen(JobExceptionOccurred::class, fn (JobExceptionOccurred $event) => $this->app->make(HorizonNode::class)->jobFinished($event->job, false));
    }

    /**
     * Admins only, without Horizon's open access in the local environment
     */
    protected function authorization(): void
    {
        Horizon::auth(fn ($request): bool => QueueStatus::available() && Gate::check('worker.horizon', [$request->user()]));
    }
}
