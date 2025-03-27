<?php

namespace App\Console;

use App\Console\Commands\MaintenanceFetchOuis;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;
use LibreNMS\Config;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Version;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $this->scheduleMarkWorking($schedule);
        $this->scheduleMaintenance($schedule);  // should be after all others
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');

        if ($this->app->environment() !== 'production') {
            require base_path('routes/dev-console.php');
        }
    }

    public function getArtisan()
    {
        if (is_null($this->artisan)) {
            parent::getArtisan();
            /** @phpstan-ignore-next-line */
            $this->artisan->setName('LibreNMS');
            /** @phpstan-ignore-next-line */
            $this->artisan->setVersion(Version::VERSION);
        }

        return $this->artisan;
    }

    public function handle($input, $output = null)
    {
        // intercept input and check for debug
        if ($input->hasParameterOption(['-d', '--debug', '-vv', '-vvv'], true)) {
            if ($input->hasParameterOption(['-vvv'], true)) {
                Debug::setVerbose();
            }
            $this->app->booted('\LibreNMS\Util\Debug::set');
        }

        return parent::handle($input, $output);
    }

    /**
     * Store in the cache that the schedule is triggered.
     * Used for Validation.
     */
    private function scheduleMarkWorking(Schedule $schedule): void
    {
        $schedule->call(function () {
            Cache::put('scheduler_working', now(), now()->addMinutes(6));
        })->everyFiveMinutes();
    }

    /**
     * Schedule maintenance tasks
     */
    private function scheduleMaintenance(Schedule $schedule): void
    {
        $maintenance_log_file = Config::get('log_dir') . '/maintenance.log';

        $schedule->command(MaintenanceFetchOuis::class, ['--wait'])
            ->weeklyOn(0, '1:00')
            ->onOneServer()
            ->appendOutputTo($maintenance_log_file);
    }
}
