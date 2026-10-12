<?php

namespace App\Providers;

use App\Facades\LibrenmsConfig;
use App\Horizon\DatastoreMetricsRepository;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Contracts\MetricsRepository;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        if ($this->app->runningInConsole()) {
            $this->configurePollingSupervisors();
        }
    }

    /**
     * Admins only, without Horizon's open access in the local environment
     */
    protected function authorization(): void
    {
        $this->gate();

        Horizon::auth(fn ($request): bool => Gate::check('workers.horizon', [$request->user()]));
    }

    /**
     * Register the Horizon gate.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', fn (User $user): bool => $user->hasRole('admin'));
    }

    /**
     * Work the poll and discovery queues of this node's poller groups, matching the queue names used by DispatchPollingWork
     */
    private function configurePollingSupervisors(): void
    {
        $groups = array_map('intval', explode(',', (string) LibrenmsConfig::get('distributed_poller_group', 0)));

        config([
            'horizon.defaults.supervisor-poll.queue' => self::queueNames('poll', $groups),
            'horizon.defaults.supervisor-poll.maxProcesses' => (int) LibrenmsConfig::get('service_poller_workers', 24),
            'horizon.defaults.supervisor-discovery.queue' => self::queueNames('discovery', $groups),
            'horizon.defaults.supervisor-discovery.maxProcesses' => (int) LibrenmsConfig::get('service_discovery_workers', 16),
        ]);
    }

    /**
     * @param  int[]  $groups
     * @return string[]
     */
    public static function queueNames(string $prefix, array $groups): array
    {
        return array_values(array_unique(array_map(fn (int $group): string => $group ? "$prefix-$group" : $prefix, $groups)));
    }
}
