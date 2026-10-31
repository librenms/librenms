<?php

namespace App\Providers;

use App\Horizon\Supervisors;
use Illuminate\Console\Application;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Console\HorizonCommand;
use Laravel\Horizon\Events\MasterSupervisorLooped;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;
use Laravel\Horizon\MasterSupervisor;

/**
 * Horizon runs the queue workers with their supervisors from the node's settings.
 * Status and statistics do not depend on it, see QueueServiceProvider.
 */
class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Supervisors::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // lnms worker runs horizon, service files use it so what runs can change without editing them
        Application::starting(fn (Application $artisan) => $artisan->addCommand($this->app->make(HorizonCommand::class)->setAliases(['worker'])));

        // name Horizon after the node so it matches the node's settings
        MasterSupervisor::$nameResolver = fn (): string => Supervisors::masterBasename();

        // only the master reads the supervisor config, LibreNMS config includes database settings once the app has booted
        Event::listen(CommandStarting::class, function (CommandStarting $event): void {
            if ($event->command == 'horizon') {
                $this->app->make(Supervisors::class)->start();
            }
        });
        Event::listen(MasterSupervisorLooped::class, fn (MasterSupervisorLooped $event) => $this->app->make(Supervisors::class)->looped($event->master));
    }

    /**
     * Admins only, without Horizon's open access in the local environment
     */
    protected function authorization(): void
    {
        Horizon::auth(fn ($request): bool => Supervisors::supported() && Gate::check('worker.horizon', [$request->user()]));
    }
}
